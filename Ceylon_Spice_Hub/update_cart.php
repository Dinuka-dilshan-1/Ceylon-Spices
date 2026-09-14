<?php
require 'db.php';
$id=(int)($_POST['id']??0);$qty=(int)($_POST['quantity']??0);
$s=$pdo->prepare("SELECT ci.cart_item_id,p.stock FROM cart_items ci JOIN carts c ON c.cart_id=ci.cart_id JOIN products p ON p.product_id=ci.product_id WHERE ci.cart_item_id=? AND c.session_token=?");
$s->execute([$id,$cartToken]);$item=$s->fetch();
if($item){if($qty<=0){$d=$pdo->prepare("DELETE FROM cart_items WHERE cart_item_id=?");$d->execute([$id]);}else{$qty=min($qty,(int)$item['stock']);$u=$pdo->prepare("UPDATE cart_items SET quantity=? WHERE cart_item_id=?");$u->execute([$qty,$id]);}}
header('Location: cart.php');exit;
?>