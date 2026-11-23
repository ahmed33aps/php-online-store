<?php
require_once __DIR__ . "/db_connect.php";
require_once __DIR__ . "/security.php";

$customer_id = $_SESSION['user_id'] ?? 0;
if (!$customer_id) {
    echo "Please login first.";
    exit;
}

// التأكد من وصول order_id عبر POST
$order_id = $_POST['order_id'] ?? 0;
if (!$order_id) {
    echo "No order selected.";
    exit;
}

// جلب الأوردر الحالي Preparing
$stmt = $conn->prepare("SELECT * FROM orders WHERE Order_ID = ? AND Customer_ID = ? AND Status = 'Preparing'");
$stmt->bind_param("ii", $order_id, $customer_id);
$stmt->execute();
$order_res = $stmt->get_result();

if ($order_res->num_rows == 0) {
    echo "Order cannot be ended or does not exist.";
    exit;
}

// تحديث الأوردر الحالي → Prepared
$update_stmt = $conn->prepare("UPDATE orders SET Status = 'Prepared' WHERE Order_ID = ?");
$update_stmt->bind_param("i", $order_id);
$update_stmt->execute();

// سجل التغيير في orderstatushistory
$insert_history = $conn->prepare("
    INSERT INTO orderstatushistory (Old_Status, New_Status, Update_Date, Customer_ID, Agent_ID, Order_ID)
    VALUES (?, ?, CURDATE(), ?, NULL, ?)
");
$old_status = 'Preparing';
$new_status = 'Prepared';
$insert_history->bind_param("ssii", $old_status, $new_status, $customer_id, $order_id);
$insert_history->execute();

$message = "<p>Order #$order_id has been ended and set to Prepared.</p>";

// التحقق من وجود أي أوردر Waiting للعميل
$waiting_stmt = $conn->prepare("SELECT * FROM orders WHERE Customer_ID = ? AND Status='Waiting'");
$waiting_stmt->bind_param("i", $customer_id);
$waiting_stmt->execute();
$waiting_res = $waiting_stmt->get_result();

if ($waiting_res->num_rows > 0) {
    $waiting_order = $waiting_res->fetch_assoc();
    $waiting_order_id = $waiting_order['Order_ID'];

    // تحويل الأوردر Waiting → Preparing
    $restore_stmt = $conn->prepare("UPDATE orders SET Status = 'Preparing' WHERE Order_ID = ?");
    $restore_stmt->bind_param("i", $waiting_order_id);
    $restore_stmt->execute();

    // سجل التغيير
    $old_status = 'Waiting';
    $new_status = 'Preparing';
    $insert_history->bind_param("ssii", $old_status, $new_status, $customer_id, $waiting_order_id);
    $insert_history->execute();

    $message .= "<p>Previous waiting order #$waiting_order_id has been restored to Preparing.</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>End Shopping</title>
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
            font-size: 2.5em;
            margin-bottom: 30px;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.7);
            text-align: center;  /* تأكيد المركز */
        }
        .message {
            background: rgba(255, 255, 255, 0.1);  /* خلفية شفافة للرسائل */
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 600px;
            margin: 0 auto 30px;  /* مركز الرسائل أفقياً */
            backdrop-filter: blur(10px);  /* تأثير ضبابي */
        }
        p {
            font-size: 1.2em;
            margin: 10px 0;
            color: #4CAF50;  /* أخضر للنجاح */
        }
        .button-group {
            display: flex;
            justify-content: center;
            gap: 20px;  /* مسافة بين الأزرار */
            flex-wrap: wrap;  /* عشان ينزلوا تحت بعض على الموبايل */
        }
        .button-group form {
            margin: 0;
            width: auto;
            max-width: none;
        }
        button {
            width: 200px;  /* عرض ثابت للأزرار */
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
    
/* Compact inline actions, keeping full-page forms unchanged. */
td form {display:inline-block;margin:0;padding:0;background:none;box-shadow:none;border:0;width:auto;max-width:none;}
td form button {margin:4px;}
</style>
</head>
<body>

<h1>End Shopping</h1>

<div class="message">
    <?php echo $message; ?>
</div>

<!-- الأزرار كأزرار أنيقة -->
<div class="button-group">
    <form action="customer_order.php" method="GET">
        <button type="submit">Back to Add products</button>
    </form>
    <form action="show_orders.php" method="GET">
        <button type="submit">Show My Orders</button>
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
