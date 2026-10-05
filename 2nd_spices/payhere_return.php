<?php
declare(strict_types=1);
require 'db.php';
require_login();
$id=(int)($_GET['order_id']??0);
$stmt=$pdo->prepare('SELECT order_id,customer_name,email,total_amount,status FROM orders WHERE order_id=? AND user_id=? LIMIT 1');
$stmt->execute([$id,(int)current_user()['user_id']]);
$o=$stmt->fetch();
if(!$o){header('Location:index.php');exit;}
$isPaid=$o['status']==='Paid';
$isPending=$o['status']==='Pending';
$title=$isPaid?'Payment Successful':($isPending?'Payment Processing':'Payment Not Completed');
$message=$isPaid?'Your PayHere payment was verified successfully. Your order is now confirmed.':($isPending?'PayHere has returned you to the store, but the server notification is still pending. Do not pay again until the status is updated.':'The PayHere payment was not completed. You can return to the shop and try again.');
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e($title)?> | Ceylon Spice Hub</title><link rel="stylesheet" href="style.css"></head><body><main class="success-page"><div class="success-card"><div class="check"><?= $isPaid ? '✓' : '!' ?></div><p class="eyebrow">PAYHERE PAYMENT</p><h1><?=e($title)?></h1><p><?=e($message)?></p><div class="order-number">Order #<?=e((string)$o['order_id'])?></div><div class="success-total"><span>Order Total</span><b><?=money((float)$o['total_amount'])?></b></div><p class="small">Current payment status: <b><?=e($o['status'])?></b></p><a class="btn btn-primary" href="<?= $isPaid ? 'order_success.php?id='.$id : 'index.php' ?>"><?= $isPaid ? 'VIEW ORDER' : 'BACK TO SHOP' ?></a></div></main></body></html>
