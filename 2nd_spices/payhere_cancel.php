<?php
declare(strict_types=1);
require 'db.php';
require_login();
$id=(int)($_GET['order_id']??0);
$stmt=$pdo->prepare('SELECT order_id,customer_name,total_amount,status FROM orders WHERE order_id=? AND user_id=? LIMIT 1');
$stmt->execute([$id,(int)current_user()['user_id']]);
$o=$stmt->fetch();
if(!$o){header('Location:index.php');exit;}
if($o['status']==='Pending'){
    $pdo->prepare("UPDATE orders SET status='Cancelled' WHERE order_id=?")->execute([$id]);
    $pdo->prepare("UPDATE payments SET status='Cancelled',status_message='Customer cancelled PayHere payment' WHERE order_id=?")->execute([$id]);
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Payment Cancelled | Ceylon Spice Hub</title><link rel="stylesheet" href="style.css"></head><body><main class="success-page"><div class="success-card"><div class="check">×</div><p class="eyebrow">PAYHERE PAYMENT</p><h1>Payment Cancelled</h1><p>Your payment was cancelled. No successful PayHere payment was recorded for this order.</p><div class="order-number">Order #<?=e((string)$o['order_id'])?></div><div class="success-total"><span>Amount</span><b><?=money((float)$o['total_amount'])?></b></div><a class="btn btn-primary" href="checkout.php">RETURN TO CHECKOUT</a></div></main></body></html>
