<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params(['httponly'=>true, 'samesite'=>'Lax', 'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
    session_start();
}
function h($value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function fail_request(string $message, int $status=400): never { http_response_code($status); exit(h($message)); }
function sql(string $query, array $values=[]): mysqli_stmt {
    global $conn;
    $stmt=$conn->prepare($query);
    if ($values) { $types=''; foreach($values as $v) $types.=is_int($v)?'i':(is_float($v)?'d':'s'); $stmt->bind_param($types, ...$values); }
    $stmt->execute(); return $stmt;
}
function rows(string $query, array $values=[]): mysqli_result { return sql($query,$values)->get_result(); }
function one(string $query, array $values=[]): ?array { return rows($query,$values)->fetch_assoc(); }
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
if (($_SERVER['REQUEST_METHOD'] ?? 'GET')==='POST' && !hash_equals($_SESSION['csrf'], (string)($_POST['csrf_token'] ?? ''))) fail_request('Invalid request token.',403);
$page=basename($_SERVER['SCRIPT_NAME'] ?? '');
$public=['index.php','login.php','register.php'];
$customer=['customer_home.php','customer_order.php','end_shopping.php','decline_order.php','reopen_order.php','show_orders.php'];
$admin=['admin_home.php','manage_agents.php','manage_categories.php','manage_products.php','edit_customer.php','edit_product.php','view_all_customers.php','view_all_orders.php','view_order_details.php','delete_order.php','delete_customer.php'];
if (!in_array($page,$public,true) && empty($_SESSION['user_id'])) {header('Location: login.php');exit;}
if ((in_array($page,$customer,true) && ($_SESSION['user_type']??'')!=='customer') || (in_array($page,$admin,true) && ($_SESSION['user_type']??'')!=='admin') || ($page==='agent_home.php' && ($_SESSION['user_type']??'')!=='agent')) fail_request('Access denied.',403);
// Add a token to the original POST forms without replacing the original frontend.
ob_start(function(string $html): string {
 return preg_replace_callback('/<form\b[^>]*method=["\']?post["\']?[^>]*>/i',fn($m)=>$m[0].'<input type="hidden" name="csrf_token" value="'.h($_SESSION['csrf']).'">',$html);
});
