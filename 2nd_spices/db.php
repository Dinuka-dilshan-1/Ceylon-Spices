<?php
declare(strict_types=1);
session_start();

$host='localhost';
$db='ceylon_spice_hub';
$user='root';
$pass='';
$charset='utf8mb4';
$options=[
    PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES=>false,
];

// Supports both the custom XAMPP port used by the uploaded project and the normal XAMPP port.
$ports=['4306','3306'];
$pdo=null;
$lastError=null;
foreach($ports as $port){
    try{
        $dsn="mysql:host={$host};port={$port};dbname={$db};charset={$charset}";
        $pdo=new PDO($dsn,$user,$pass,$options);
        $connectedPort=$port;
        break;
    }catch(PDOException $e){$lastError=$e;}
}
if(!$pdo){
    http_response_code(500);
    die('<div style="font-family:Arial;padding:40px;max-width:700px;margin:auto"><h2>Ceylon Spice Hub - Database Connection Error</h2><p>Start MySQL in XAMPP and import <b>database.sql</b> in phpMyAdmin.</p><p>This version checks ports <b>4306</b> and <b>3306</b> automatically.</p></div>');
}

if(empty($_SESSION['cart_token'])) $_SESSION['cart_token']=bin2hex(random_bytes(24));
$cartToken=$_SESSION['cart_token'];

function e(?string $value):string{return htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');}
function money(float $value):string{return 'Rs. '.number_format($value,2);}
function csrf_token():string{if(empty($_SESSION['csrf']))$_SESSION['csrf']=bin2hex(random_bytes(32));return $_SESSION['csrf'];}
function verify_csrf():void{if(!hash_equals($_SESSION['csrf']??'',$_POST['csrf']??'')){http_response_code(419);exit('Invalid form token. Please go back and try again.');}}
function current_user():?array{
    global $pdo;
    static $checked = false;
    static $cached = null;

    if($checked) return $cached;
    $checked = true;

    $sessionUser = $_SESSION['user'] ?? null;
    if(!$sessionUser || empty($sessionUser['user_id'])) return null;

    // A stale login session can remain after the database is re-imported or
    // a user is deleted. Never use that old user_id in a foreign-key insert.
    $stmt = $pdo->prepare('SELECT user_id,name,email,role FROM users WHERE user_id=? LIMIT 1');
    $stmt->execute([(int)$sessionUser['user_id']]);
    $fresh = $stmt->fetch();

    if(!$fresh){
        unset($_SESSION['user']);
        $cached = null;
        return null;
    }

    $cached = [
        'user_id'=>(int)$fresh['user_id'],
        'name'=>$fresh['name'],
        'email'=>$fresh['email'],
        'role'=>$fresh['role'] ?? 'customer'
    ];
    $_SESSION['user']=$cached;
    return $cached;
}
function require_login():void{if(!current_user()){$_SESSION['return_to']=$_SERVER['REQUEST_URI']??'index.php';header('Location: login.php');exit;}}
function flash(string $key,?string $value=null):?string{if($value!==null){$_SESSION['flash'][$key]=$value;return null;} $v=$_SESSION['flash'][$key]??null;unset($_SESSION['flash'][$key]);return $v;}
function login_user(array $u):void{$_SESSION['user']=['user_id'=>(int)$u['user_id'],'name'=>$u['name'],'email'=>$u['email'],'role'=>$u['role']??'customer'];session_regenerate_id(true);}
function logout_user():void{unset($_SESSION['user']);session_regenerate_id(true);}

/** Return the cart belonging to the current user, or the current browser session. */
function get_active_cart_id():?int{
    global $pdo,$cartToken;
    $uid=current_user()['user_id']??null;

    if($uid){
        $userStmt=$pdo->prepare('SELECT cart_id FROM carts WHERE user_id=? ORDER BY cart_id DESC LIMIT 1');
        $userStmt->execute([(int)$uid]);
        $userCart=$userStmt->fetchColumn();

        $guestStmt=$pdo->prepare('SELECT cart_id FROM carts WHERE session_token=? LIMIT 1');
        $guestStmt->execute([$cartToken]);
        $guestCart=$guestStmt->fetchColumn();

        // Merge the browser cart into the account cart after login.
        if($guestCart && $userCart && (int)$guestCart !== (int)$userCart){
            $items=$pdo->prepare('SELECT product_id,quantity FROM cart_items WHERE cart_id=?');
            $items->execute([(int)$guestCart]);
            $find=$pdo->prepare('SELECT cart_item_id,quantity FROM cart_items WHERE cart_id=? AND product_id=? LIMIT 1');
            $update=$pdo->prepare('UPDATE cart_items SET quantity=? WHERE cart_item_id=?');
            $insert=$pdo->prepare('INSERT INTO cart_items(cart_id,product_id,quantity) VALUES(?,?,?)');
            $stockStmt=$pdo->prepare('SELECT stock FROM products WHERE product_id=?');
            foreach($items as $item){
                $stockStmt->execute([(int)$item['product_id']]);
                $stock=(int)$stockStmt->fetchColumn();
                if($stock<1) continue;
                $find->execute([(int)$userCart,(int)$item['product_id']]);
                $existing=$find->fetch();
                if($existing){
                    $qty=min($stock,(int)$existing['quantity']+(int)$item['quantity']);
                    $update->execute([$qty,(int)$existing['cart_item_id']]);
                }else{
                    $insert->execute([(int)$userCart,(int)$item['product_id'],min($stock,(int)$item['quantity'])]);
                }
            }
            $pdo->prepare('DELETE FROM carts WHERE cart_id=?')->execute([(int)$guestCart]);
            return (int)$userCart;
        }

        if($userCart) return (int)$userCart;

        if($guestCart){
            $pdo->prepare('UPDATE carts SET user_id=?,session_token=NULL WHERE cart_id=?')->execute([(int)$uid,(int)$guestCart]);
            return (int)$guestCart;
        }

        $pdo->prepare('INSERT INTO carts(user_id,session_token) VALUES(?,NULL)')->execute([(int)$uid]);
        return (int)$pdo->lastInsertId();
    }

    $stmt=$pdo->prepare('SELECT cart_id FROM carts WHERE session_token=? LIMIT 1');
    $stmt->execute([$cartToken]);
    $cart=$stmt->fetchColumn();
    return $cart ? (int)$cart : null;
}
?>
