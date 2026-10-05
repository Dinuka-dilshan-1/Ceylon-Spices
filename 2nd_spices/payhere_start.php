<?php
declare(strict_types=1);
require 'db.php';
require 'payhere_config.php';

require_login();
$orderId = (int)($_GET['order_id'] ?? 0);
if ($orderId < 1) {
    header('Location: checkout.php');
    exit;
}

$stmt = $pdo->prepare('SELECT o.*, p.method AS payment_method, p.status AS payment_status FROM orders o LEFT JOIN payments p ON p.order_id=o.order_id WHERE o.order_id=? AND o.user_id=? LIMIT 1');
$stmt->execute([$orderId, (int)current_user()['user_id']]);
$order = $stmt->fetch();
if (!$order || $order['payment_method'] !== 'PayHere') {
    header('Location: checkout.php');
    exit;
}

$amount = number_format((float)$order['total_amount'], 2, '.', '');
$currency = 'LKR';
$hash = payhere_hash(PAYHERE_MERCHANT_ID, (string)$orderId, (float)$order['total_amount'], $currency, PAYHERE_MERCHANT_SECRET);
$fullName = trim((string)$order['customer_name']);
$parts = preg_split('/\s+/', $fullName, 2);
$firstName = $parts[0] ?? $fullName;
$lastName = $parts[1] ?? 'Customer';
$itemsStmt = $pdo->prepare('SELECT oi.quantity, oi.price, p.name FROM order_items oi JOIN products p ON p.product_id=oi.product_id WHERE oi.order_id=?');
$itemsStmt->execute([$orderId]);
$items = $itemsStmt->fetchAll();
$itemTitle = implode(', ', array_map(fn($x) => $x['name'] . ' x' . $x['quantity'], $items));
if ($itemTitle === '') $itemTitle = 'Ceylon Spice Hub Order #' . $orderId;
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Redirecting to PayHere | Ceylon Spice Hub</title>
<link rel="stylesheet" href="style.css">
<style>
body{background:#fbf8f1}.payhere-loading{min-height:100vh;display:grid;place-items:center;padding:24px}.payhere-card{max-width:520px;width:100%;background:#fff;border:1px solid #e7e3da;padding:48px;text-align:center;box-shadow:0 20px 60px rgba(20,40,30,.08)}.payhere-spinner{width:42px;height:42px;border:3px solid #dfe8e2;border-top-color:#0d5135;border-radius:50%;animation:spin 1s linear infinite;margin:0 auto 22px}@keyframes spin{to{transform:rotate(360deg)}}.payhere-card p{color:#68706b;font-size:14px;line-height:1.7}
</style>
</head>
<body>
<div class="payhere-loading">
  <div class="payhere-card">
    <div class="payhere-spinner"></div>
    <p class="eyebrow">SECURE PAYMENT</p>
    <h1>Connecting to PayHere</h1>
    <p>Order #<?=e((string)$orderId)?> · <?=e($amount)?> LKR</p>
    <p>Please wait. You will be redirected to the secure PayHere payment page.</p>
    <form id="payhereForm" method="post" action="<?=e(payhere_checkout_url())?>">
      <input type="hidden" name="merchant_id" value="<?=e((string)PAYHERE_MERCHANT_ID)?>">
      <input type="hidden" name="return_url" value="<?=e(payhere_url('payhere_return.php?order_id=' . $orderId))?>">
      <input type="hidden" name="cancel_url" value="<?=e(payhere_url('payhere_cancel.php?order_id=' . $orderId))?>">
      <input type="hidden" name="notify_url" value="<?=e(payhere_url('payhere_notify.php'))?>">
      <input type="hidden" name="first_name" value="<?=e($firstName)?>">
      <input type="hidden" name="last_name" value="<?=e($lastName)?>">
      <input type="hidden" name="email" value="<?=e((string)$order['email'])?>">
      <input type="hidden" name="phone" value="<?=e((string)$order['contact'])?>">
      <input type="hidden" name="address" value="<?=e((string)$order['address'])?>">
      <input type="hidden" name="city" value="<?=e((string)$order['city'])?>">
      <input type="hidden" name="country" value="Sri Lanka">
      <input type="hidden" name="order_id" value="<?=e((string)$orderId)?>">
      <input type="hidden" name="items" value="<?=e($itemTitle)?>">
      <input type="hidden" name="currency" value="LKR">
      <input type="hidden" name="amount" value="<?=e($amount)?>">
      <input type="hidden" name="hash" value="<?=e($hash)?>">
<?php foreach ($items as $n => $item): ?>
      <input type="hidden" name="item_name_<?=($n+1)?>" value="<?=e((string)$item['name'])?>">
      <input type="hidden" name="item_number_<?=($n+1)?>" value="<?=e('P' . $orderId . '-' . ($n+1))?>">
      <input type="hidden" name="amount_<?=($n+1)?>" value="<?=e(number_format((float)$item['price'],2,'.',''))?>">
      <input type="hidden" name="quantity_<?=($n+1)?>" value="<?=e((string)$item['quantity'])?>">
<?php endforeach; ?>
      <noscript><button class="btn btn-primary" type="submit">CONTINUE TO PAYHERE</button></noscript>
    </form>
  </div>
</div>
<script>document.getElementById('payhereForm').submit();</script>
</body>
</html>
