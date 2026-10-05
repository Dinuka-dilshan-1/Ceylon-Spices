<?php
declare(strict_types=1);
require 'db.php';
require 'payhere_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$merchantId = trim((string)($_POST['merchant_id'] ?? ''));
$orderId = trim((string)($_POST['order_id'] ?? ''));
$amount = trim((string)($_POST['payhere_amount'] ?? ''));
$currency = trim((string)($_POST['payhere_currency'] ?? ''));
$statusCode = trim((string)($_POST['status_code'] ?? ''));
$md5sig = strtoupper(trim((string)($_POST['md5sig'] ?? '')));
$method = trim((string)($_POST['method'] ?? 'PayHere'));
$statusMessage = trim((string)($_POST['status_message'] ?? ''));

if ($merchantId !== (string)PAYHERE_MERCHANT_ID || $orderId === '' || $amount === '' || $currency === '' || $statusCode === '' || $md5sig === '') {
    http_response_code(400);
    exit('Invalid notification');
}

$expected = payhere_notify_hash($merchantId, $orderId, $amount, $currency, $statusCode, PAYHERE_MERCHANT_SECRET);
if (!hash_equals($expected, $md5sig)) {
    http_response_code(403);
    exit('Invalid checksum');
}

$localStatus = match ($statusCode) {
    '2' => 'Paid',
    '0' => 'Pending',
    '-1' => 'Cancelled',
    '-2' => 'Failed',
    '-3' => 'Chargedback',
    default => 'Pending',
};

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('SELECT order_id,total_amount,status,user_id FROM orders WHERE order_id=? FOR UPDATE');
    $stmt->execute([(int)$orderId]);
    $order = $stmt->fetch();
    if (!$order) {
        $pdo->rollBack();
        http_response_code(404);
        exit('Order not found');
    }

    // Verify the amount received from PayHere against our order amount.
    if (number_format((float)$order['total_amount'], 2, '.', '') !== number_format((float)$amount, 2, '.', '')) {
        $pdo->rollBack();
        http_response_code(400);
        exit('Amount mismatch');
    }

    $paymentStmt = $pdo->prepare('UPDATE payments SET status=?, method=?, transaction_id=?, status_message=? WHERE order_id=?');
    $paymentStmt->execute([$localStatus, 'PayHere - ' . $method, $md5sig, $statusMessage, (int)$orderId]);

    if ($statusCode === '2' && $order['status'] !== 'Paid') {
        $itemsStmt = $pdo->prepare('SELECT oi.product_id,oi.quantity,p.stock FROM order_items oi JOIN products p ON p.product_id=oi.product_id WHERE oi.order_id=? FOR UPDATE');
        $itemsStmt->execute([(int)$orderId]);
        $items = $itemsStmt->fetchAll();
        foreach ($items as $item) {
            if ((int)$item['stock'] < (int)$item['quantity']) {
                throw new RuntimeException('Insufficient stock for paid order #' . $orderId);
            }
        }
        $stockStmt = $pdo->prepare('UPDATE products SET stock=stock-? WHERE product_id=?');
        foreach ($items as $item) {
            $stockStmt->execute([(int)$item['quantity'], (int)$item['product_id']]);
        }
        $pdo->prepare("UPDATE orders SET status='Paid' WHERE order_id=?")->execute([(int)$orderId]);

        // Remove only this user's/session cart items matching the paid order.
        $uid = $order['user_id'] !== null ? (int)$order['user_id'] : 0;
        if ($uid > 0) {
            $cartStmt = $pdo->prepare('SELECT cart_id FROM carts WHERE user_id=? ORDER BY cart_id DESC LIMIT 1');
            $cartStmt->execute([$uid]);
            $cartId = $cartStmt->fetchColumn();
            if ($cartId) {
                $pdo->prepare('DELETE FROM cart_items WHERE cart_id=? AND product_id IN (SELECT product_id FROM order_items WHERE order_id=?)')->execute([(int)$cartId, (int)$orderId]);
            }
        }
    } elseif (in_array($statusCode, ['-1','-2','-3'], true) && $order['status'] !== 'Paid') {
        $pdo->prepare('UPDATE orders SET status=? WHERE order_id=?')->execute([$localStatus, (int)$orderId]);
    } else {
        $pdo->prepare('UPDATE orders SET status=? WHERE order_id=?')->execute([$localStatus, (int)$orderId]);
    }

    $pdo->commit();
    http_response_code(200);
    echo 'OK';
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo 'Server error';
}
