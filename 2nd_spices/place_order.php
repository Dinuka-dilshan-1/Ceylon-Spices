<?php
declare(strict_types=1);
require 'db.php';
require_login();
verify_csrf();
if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location:index.php');exit;}

$uid=(int)current_user()['user_id'];
$name=trim($_POST['customer_name']??'');
$email=trim($_POST['email']??'');
$contact=trim($_POST['contact']??'');
$city=trim($_POST['city']??'');
$address=trim($_POST['address']??'');
$method=trim($_POST['payment_method']??'Cash on Delivery');
$allowed=['Cash on Delivery','Bank Transfer','PayHere'];
if(!in_array($method,$allowed,true)) $method='Cash on Delivery';

if(!$name||!filter_var($email,FILTER_VALIDATE_EMAIL)||!$contact||!$city||!$address){
    die('Please complete all checkout fields. <a href="checkout.php">Go back</a>');
}

$cartId=get_active_cart_id();
if(!$cartId){header('Location:cart.php');exit;}

$s=$pdo->prepare("SELECT ci.product_id,ci.quantity,p.name,p.price,p.stock FROM cart_items ci JOIN products p ON p.product_id=ci.product_id WHERE ci.cart_id=?");
$s->execute([$cartId]);
$items=$s->fetchAll();
if(!$items){header('Location:cart.php');exit;}

$subtotal=0.0;
foreach($items as $i){
    if((int)$i['quantity']>(int)$i['stock']) die('One of the selected products no longer has enough stock. <a href="cart.php">Return to cart</a>');
    $subtotal+=(float)$i['price']*(int)$i['quantity'];
}
$total=$subtotal+250;

try{
    $pdo->beginTransaction();

    // PayHere orders remain Pending until the server-side PayHere notification is verified.
    // Stock is intentionally reduced only after a verified status_code=2 callback.
    $orderStatus = $method==='PayHere' ? 'Pending' : 'Pending';
    $s=$pdo->prepare("INSERT INTO orders(user_id,customer_name,email,contact,address,city,total_amount,status) VALUES(?,?,?,?,?,?,?,?)");
    $s->execute([$uid,$name,$email,$contact,$address,$city,$total,$orderStatus]);
    $orderId=(int)$pdo->lastInsertId();

    $iStmt=$pdo->prepare("INSERT INTO order_items(order_id,product_id,quantity,price) VALUES(?,?,?,?)");
    foreach($items as $i){$iStmt->execute([$orderId,$i['product_id'],$i['quantity'],$i['price']]);}

    $pStmt=$pdo->prepare("INSERT INTO payments(order_id,amount,method,transaction_id,status,status_message) VALUES(?,?,?,?,?,?)");
    if($method==='PayHere'){
        $pStmt->execute([$orderId,$total,'PayHere',null,'Pending','Waiting for PayHere payment notification']);
    }else{
        $pStmt->execute([$orderId,$total,$method,$method==='Cash on Delivery'?'COD-'.$orderId:'BANK-'.$orderId,'Pending',null]);
    }

    if($method!=='PayHere'){
        $uStmt=$pdo->prepare("UPDATE products SET stock=stock-? WHERE product_id=? AND stock>=?");
        foreach($items as $i){
            $uStmt->execute([(int)$i['quantity'],(int)$i['product_id'],(int)$i['quantity']]);
            if($uStmt->rowCount()!==1) throw new RuntimeException('Stock changed while placing the order.');
        }
        $d=$pdo->prepare('DELETE FROM cart_items WHERE cart_id=?');
        $d->execute([$cartId]);
    }

    $pdo->commit();

    if($method==='PayHere'){
        header('Location: payhere_start.php?order_id='.$orderId);
    }else{
        header('Location:order_success.php?id='.$orderId);
    }
    exit;
}catch(Throwable $e){
    if($pdo->inTransaction())$pdo->rollBack();
    http_response_code(500);
    die('Unable to place the order. Please try again.');
}
?>
