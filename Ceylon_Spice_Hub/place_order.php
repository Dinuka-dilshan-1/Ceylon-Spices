<?php
require 'db.php';
if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location:index.php');exit;}
$name=trim($_POST['customer_name']??'');$email=trim($_POST['email']??'');$contact=trim($_POST['contact']??'');$city=trim($_POST['city']??'');$address=trim($_POST['address']??'');$method=trim($_POST['payment_method']??'Cash on Delivery');
if(!$name||!filter_var($email,FILTER_VALIDATE_EMAIL)||!$contact||!$city||!$address){die('Please complete all checkout fields. <a href="checkout.php">Go back</a>');}
$s=$pdo->prepare("SELECT c.cart_id FROM carts c WHERE c.session_token=?");$s->execute([$cartToken]);$cart=$s->fetch();
if(!$cart){header('Location:cart.php');exit;}
$s=$pdo->prepare("SELECT ci.product_id,ci.quantity,p.name,p.price,p.stock FROM cart_items ci JOIN products p ON p.product_id=ci.product_id WHERE ci.cart_id=?");$s->execute([$cart['cart_id']]);$items=$s->fetchAll();
if(!$items){header('Location:cart.php');exit;}
$subtotal=0;foreach($items as $i){if($i['quantity']>$i['stock'])die('One of the selected products no longer has enough stock. <a href="cart.php">Return to cart</a>');$subtotal+=$i['price']*$i['quantity'];}
$total=$subtotal+250;
try{$pdo->beginTransaction();
$s=$pdo->prepare("INSERT INTO orders(customer_name,email,contact,address,city,total_amount,status) VALUES(?,?,?,?,?,?,'Pending')");$s->execute([$name,$email,$contact,$address,$city,$total]);$orderId=(int)$pdo->lastInsertId();
$iStmt=$pdo->prepare("INSERT INTO order_items(order_id,product_id,quantity,price) VALUES(?,?,?,?)");$uStmt=$pdo->prepare("UPDATE products SET stock=stock-? WHERE product_id=?");
foreach($items as $i){$iStmt->execute([$orderId,$i['product_id'],$i['quantity'],$i['price']]);$uStmt->execute([$i['quantity'],$i['product_id']]);}
$pStmt=$pdo->prepare("INSERT INTO payments(order_id,amount,method,transaction_id) VALUES(?,?,?,?)");$pStmt->execute([$orderId,$total,$method,'COD-'.$orderId]);
$d=$pdo->prepare("DELETE FROM cart_items WHERE cart_id=?");$d->execute([$cart['cart_id']]);$pdo->commit();
header("Location:order_success.php?id=".$orderId);exit;
}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();http_response_code(500);die('Unable to place the order. Please try again.');}
?>