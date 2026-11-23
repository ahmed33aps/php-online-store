<?php
require_once __DIR__ . "/security.php";
require_once __DIR__ . "/db_connect.php";

// التأكد أن المستخدم عامل login و admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: index.php");
    exit();
}

$admin_id = $_SESSION['user_id'];

// جلب اسم الادمن
$user = $conn->query("SELECT First_Name, Last_Name FROM users WHERE User_ID=$admin_id")->fetch_assoc();
$first = $user['First_Name'];
$last = $user['Last_Name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Administrator Home</title>
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
        .buttons-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;  /* مسافة بين الأزرار */
            max-width: 400px;
            margin: 0 auto;
        }
        form {
            width: 100%;
        }
        button {
            width: 100%;
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
        }
        button:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 12px 20px rgba(0,0,0,0.4);
        }
        .back-link {
            margin-top: 30px;
            display: inline-block;
        }
        .back-link button {
            width: 200px;  /* عرض أصغر للزر السفلي */
        }
    
/* Compact inline actions, keeping full-page forms unchanged. */
td form {display:inline-block;margin:0;padding:0;background:none;box-shadow:none;border:0;width:auto;max-width:none;}
td form button {margin:4px;}
</style>
</head>
<body>

<h1>Welcome, <?= h($first) ?> <?= h($last) ?>!</h1>

<!-- أزرار الادمن الرئيسية في حاوية منظمة -->
<div class="buttons-container">
    <form action="view_all_customers.php" method="GET">
        <button type="submit">View All Customers</button>
    </form>

    <form action="view_all_orders.php" method="GET">
        <button type="submit">View All Orders</button>
    </form>

    <form action="manage_categories.php" method="GET">
        <button type="submit">Manage category</button>
    </form>
    
    <form action="manage_products.php" method="GET">
        <button type="submit">Manage products</button>
    </form>

    <form action="manage_agents.php" method="GET">
        <button type="submit">Manage Agents</button>
    </form>

    <form action="update_info.php" method="GET">
        <button type="submit">My Info</button>
    </form>
</div>

<!-- الرابط السفلي كزر أنيق -->
<div class="back-link">
    <form action="index.php" method="GET">
        <button type="submit">Go to registration page</button>
    </form>
</div>

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
