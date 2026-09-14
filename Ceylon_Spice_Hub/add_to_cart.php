<?php
require 'db.php'; header('Content-Type: application/json');
$id=(int)($_POST['product_id']??0);$qty=max(1,(int)($_POST['quantity']??1));
$s=$pdo->prepare('SELECT product_id,name,stock FROM products WHERE product_id=?');$s->execute([$id]);$p=$s->fetch();
if(!$p||$p['stock']<1){echo json_encode(['success'=>false,'message'=>'Product is out of stock.']);exit;}
$s=$pdo->prepare('SELECT cart_id FROM carts WHERE session_token=?');$s->execute([$cartToken]);$cart=$s->fetch();
if(!$cart){$s=$pdo->prepare('INSERT INTO carts(session_token) VALUES(?)');$s->execute([$cartToken]);$cartId=(int)$pdo->lastInsertId();}else $cartId=(int)$cart['cart_id'];
$s=$pdo->prepare('SELECT cart_item_id,quantity FROM cart_items WHERE cart_id=? AND product_id=?');$s->execute([$cartId,$id]);$item=$s->fetch();
if($item){$new=min((int)$p['stock'],(int)$item['quantity']+$qty);$u=$pdo->prepare('UPDATE cart_items SET quantity=? WHERE cart_item_id=?');$u->execute([$new,$item['cart_item_id']]);}
else{$i=$pdo->prepare('INSERT INTO cart_items(cart_id,product_id,quantity) VALUES(?,?,?)');$i->execute([$cartId,$id,min((int)$p['stock'],$qty)]);}
$s=$pdo->prepare('SELECT COALESCE(SUM(quantity),0) FROM cart_items WHERE cart_id=?');$s->execute([$cartId]);
echo json_encode(['success'=>true,'count'=>(int)$s->fetchColumn()]);
?>