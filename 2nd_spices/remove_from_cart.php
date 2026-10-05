<?php
require 'db.php';
$id=(int)($_GET['id']??0);
$cartId=get_active_cart_id();
if($cartId){$s=$pdo->prepare('DELETE FROM cart_items WHERE cart_item_id=? AND cart_id=?');$s->execute([$id,$cartId]);}
header('Location:cart.php');exit;
