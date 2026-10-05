<?php
declare(strict_types=1);
require 'db.php';

$isPost=(($_SERVER['REQUEST_METHOD']??'GET')==='POST');
if(!$isPost){header('Location:index.php#shop');exit;}

$id=(int)($_POST['product_id']??0);
$qty=max(1,(int)($_POST['quantity']??1));
$redirect=isset($_POST['redirect']) && $_POST['redirect']==='1';
$isAjax=isset($_POST['ajax']) && $_POST['ajax']==='1';

try{
    $s=$pdo->prepare('SELECT product_id,name,stock FROM products WHERE product_id=? LIMIT 1');
    $s->execute([$id]);
    $product=$s->fetch();
    if(!$product) throw new RuntimeException('Product not found.');

    $stock=(int)$product['stock'];
    if($stock<1) throw new RuntimeException('This product is currently out of stock.');

    $cartId=get_active_cart_id();
    if(!$cartId){
        if(current_user()){
            $s=$pdo->prepare('INSERT INTO carts(user_id,session_token) VALUES(?,NULL)');
            $s->execute([(int)current_user()['user_id']]);
        }else{
            $s=$pdo->prepare('INSERT INTO carts(user_id,session_token) VALUES(NULL,?)');
            $s->execute([$cartToken]);
        }
        $cartId=(int)$pdo->lastInsertId();
    }

    $s=$pdo->prepare('SELECT cart_item_id,quantity FROM cart_items WHERE cart_id=? AND product_id=? LIMIT 1');
    $s->execute([$cartId,$id]);
    $item=$s->fetch();

    if($item){
        $newQty=min($stock,(int)$item['quantity']+$qty);
        $u=$pdo->prepare('UPDATE cart_items SET quantity=? WHERE cart_item_id=?');
        $u->execute([$newQty,(int)$item['cart_item_id']]);
    }else{
        $newQty=min($stock,$qty);
        $i=$pdo->prepare('INSERT INTO cart_items(cart_id,product_id,quantity) VALUES(?,?,?)');
        $i->execute([$cartId,$id,$newQty]);
    }

    $s=$pdo->prepare('SELECT COALESCE(SUM(quantity),0) FROM cart_items WHERE cart_id=?');
    $s->execute([$cartId]);
    $count=(int)$s->fetchColumn();

    if($redirect){header('Location:cart.php');exit;}
    if(!$isAjax){
        header('Location:index.php#shop');exit;
    }

    header('Content-Type:application/json; charset=utf-8');
    echo json_encode(['success'=>true,'count'=>$count,'message'=>$product['name'].' added to your cart.']);
}catch(Throwable $e){
    if($redirect || !$isAjax){
        flash('cart_error',$e->getMessage());
        header('Location:index.php#shop');
        exit;
    }
    http_response_code(400);
    header('Content-Type:application/json; charset=utf-8');
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
