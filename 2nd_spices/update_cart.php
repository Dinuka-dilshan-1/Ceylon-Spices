<?php
declare(strict_types=1);
require 'db.php';
if(($_SERVER['REQUEST_METHOD']??'GET')!=='POST'){header('Location:cart.php');exit;}
$id=(int)($_POST['id']??0);
$qty=(int)($_POST['quantity']??0);
$cartId=get_active_cart_id();
if($cartId){
    $s=$pdo->prepare('SELECT ci.cart_item_id,p.stock FROM cart_items ci JOIN products p ON p.product_id=ci.product_id WHERE ci.cart_item_id=? AND ci.cart_id=? LIMIT 1');
    $s->execute([$id,$cartId]);
    $item=$s->fetch();
    if($item){
        if($qty<=0){$d=$pdo->prepare('DELETE FROM cart_items WHERE cart_item_id=? AND cart_id=?');$d->execute([$id,$cartId]);}
        else{$qty=min($qty,(int)$item['stock']);if($qty>0){$u=$pdo->prepare('UPDATE cart_items SET quantity=? WHERE cart_item_id=? AND cart_id=?');$u->execute([$qty,$id,$cartId]);}}
    }
}
header('Location:cart.php');exit;
