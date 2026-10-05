<?php
require 'db.php';header('Content-Type: application/json');
$email=trim($_POST['email']??'');
if(!filter_var($email,FILTER_VALIDATE_EMAIL)){echo json_encode(['success'=>false,'message'=>'Please enter a valid email address.']);exit;}
try{$s=$pdo->prepare("INSERT INTO subscribers(email) VALUES(?)");$s->execute([$email]);echo json_encode(['success'=>true,'message'=>'Thanks! You are now subscribed.']);}
catch(PDOException $e){if($e->getCode()==='23000')echo json_encode(['success'=>true,'message'=>'You are already subscribed.']);else echo json_encode(['success'=>false,'message'=>'Subscription failed.']);}
?>