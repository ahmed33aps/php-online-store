<?php
require_once __DIR__ . "/security.php";
require_once __DIR__ . "/db_connect.php";

// التأكد أن المستخدم عامل login و customer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'customer') {
    header("Location: index.php");
    exit();
}

$customer_id = $_SESSION['user_id'];

// جلب اسم العميل من جدول users
$user = $conn->query("SELECT First_Name, Last_Name FROM users WHERE User_ID=$customer_id")->fetch_assoc();
$first = $user['First_Name'];
$last = $user['Last_Name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Home</title>
    <style>
        /* CSS لديزاين أنيق وشياك مع إصلاح السكرول والنصوص */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a2e, #16213e, #0f3460);  /* تدرج داكن أنيق */
            color: #e9ecef;
            margin: 0;
            padding: 20px;  /* padding عشان ما يلتصق المحتوى بالحواف */
            min-height: 100vh;  /* min-height بدل height عشان يسمح بالسكرول */
            text-align: center;  /* مركز النصوص */
            position: relative;
            overflow-y: auto;  /* سكرول عمودي إذا لزم */
        }
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 25% 25%, rgba(255,255,255,.05), transparent 60%);
            background-size: cover;
            opacity: 0.1;
            z-index: -1;
        }
        h1 {
            color: #ffd700;
            font-size: 3em;
            margin-bottom: 30px;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.7);
            text-align: center;  /* تأكيد المركز */
        }
        form {
            margin-bottom: 20px;
            display: inline-block;  /* عشان الأزرار تكون في سطر واحد أو منظمة */
        }
        button {
            width: 300px;  /* عرض ثابت للأزرار عشان تكون منتظمة */
            padding: 15px;
            background: linear-gradient(45deg, #ff6b6b, #feca57);
            color: white;
            border: none;
            border-radius: 30px;
            font-size: 1.2em;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.4s ease;
            box-shadow: 0 8px 15px rgba(0,0,0,0.3);
            margin: 10px;  /* مسافة بين الأزرار */
        }
        button:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 12px 20px rgba(0,0,0,0.4);
        }
        a {
            display: inline-block;
            margin-top: 30px;
            color: #ffd700;
            text-decoration: none;
            font-size: 1.1em;
        }
        a:hover {
            text-decoration: underline;
        }
    
/* Compact inline actions, keeping full-page forms unchanged. */
td form {display:inline-block;margin:0;padding:0;background:none;box-shadow:none;border:0;width:auto;max-width:none;}
td form button {margin:4px;}
</style>
</head>
<body>

<h1>Welcome, <?= h($first) ?> <?= h($last) ?>!</h1>

<!-- 3 أزرار رئيسية -->
<form action="show_orders.php" method="GET">
    <button type="submit">Show My Orders</button>
</form>

<form action="customer_order.php" method="GET">
    <button type="submit">Add products to Order</button>
</form>

<form action="update_info.php" method="GET">
    <button type="submit">My Info</button>
</form>

<form action="index.php" method="GET">
    <button type="submit">Go to Registration Page</button>
</form>


<script>
    // تأثير fade-in للصفحة
    document.addEventListener('DOMContentLoaded', function() {
        document.body.style.opacity = '0';
        setTimeout(() => {
            document.body.style.transition = 'opacity 1s';
            document.body.style.opacity = '1';
        }, 100);
    });
</script>

</body>
</html>
