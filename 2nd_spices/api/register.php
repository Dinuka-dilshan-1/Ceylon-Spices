<?php
require '../db.php';
header('Content-Type: application/json; charset=utf-8');
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);echo json_encode(['success'=>false,'message'=>'POST required']);exit;}
$data=json_decode(file_get_contents('php://input'),true)??$_POST;
$name=trim($data['name']??'');$email=strtolower(trim($data['email']??''));$pw=$data['password']??'';
if($name===''||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($pw)<8){http_response_code(422);echo json_encode(['success'=>false,'message'=>'Invalid registration data.']);exit;}
$s=$pdo->prepare('SELECT user_id FROM users WHERE email=?');$s->execute([$email]);
if($s->fetch()){http_response_code(409);echo json_encode(['success'=>false,'message'=>'Unable to create the account with these details.']);exit;}
try{
  $pdo->beginTransaction();
  $s=$pdo->prepare('INSERT INTO users(name,email,password_hash,role) VALUES(?,?,?,?)');
  $s->execute([$name,$email,password_hash($pw,PASSWORD_BCRYPT),'customer']);
  $uid=(int)$pdo->lastInsertId();
  $pdo->prepare('INSERT INTO profiles(user_id) VALUES(?)')->execute([$uid]);
  $pdo->commit();
  http_response_code(201);echo json_encode(['success'=>true,'user_id'=>$uid]);
}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();http_response_code(500);echo json_encode(['success'=>false,'message'=>'Registration could not be completed.']);}
