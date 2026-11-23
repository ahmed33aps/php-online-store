<?php
require_once __DIR__ . "/security.php";
require_once __DIR__ . "/db_connect.php";

// التأكد أن المستخدم admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: index.php");
    exit();
}

// التأكد من وجود order_id
if (!isset($_GET['order_id'])) {
    $message = "No order selected.";
    $error = true;
} else {
    $order_id = intval($_GET['order_id']);

    // جلب بيانات الأوردر مع اسم العميل
    $order_sql = "
    SELECT o.Order_ID, o.Order_Date, o.Status, u.First_Name, u.Last_Name
    FROM orders o
    JOIN users u ON o.Customer_ID = u.User_ID
    WHERE o.Order_ID = $order_id
    ";
    $order_res = $conn->query($order_sql);
    $order = $order_res->fetch_assoc();

    // جلب تفاصيل المنتجات داخل الأوردر
    $items_sql = "
    SELECT oi.Product_ID, p.Product_Name, oi.quantity, oi.unit_price, oi.total_price
    FROM order_items oi
    JOIN products p ON oi.Product_ID = p.Product_ID
    WHERE oi.Order_ID = $order_id
    ";
    $items_res = $conn->query($items_sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Details</title>
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
        h2 {
            color: #ffd700;
            font-size: 2.5em;
            margin-bottom: 30px;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.7);
            text-align: center;  /* تأكيد المركز */
        }
        p {
            font-size: 1.2em;
            margin: 10px 0;
        }
        table {
            width: 100%;
            max-width: 1000px;
            margin: 20px auto;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            backdrop-filter: blur(10px);
        }
        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        th {
            background: rgba(255, 255, 255, 0.2);
            color: #ffd700;
            font-weight: bold;
        }
        tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        tr:last-child td {
            border-bottom: none;
        }
        tr:last-child {
            background: rgba(255, 255, 255, 0.2);
            font-weight: bold;
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
            color: #ff6b6b;  /* أحمر للأخطاء */
            font-weight: bold;
        }
        form {
            display: inline-block;
            margin: 0;
        }
        button {
            width: 200px;  /* عرض ثابت للزر */
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

<h2>Order Details - Order #<?= h($order['Order_ID'] ?? 'N/A') ?></h2>

<?php if(isset($error) && $error): ?>
<div class="message">
    <?php echo $message; ?>
</div>
<?php else: ?>
<p>Customer: <?= h($order['First_Name'] . " " . $order['Last_Name']) ?></p>
<p>Order Date: <?= h($order['Order_Date']) ?></p>
<p>Status: <?= h($order['Status']) ?></p>

<table>
    <tr>
        <th>Product ID</th>
        <th>Product Name</th>
        <th>Quantity</th>
        <th>Unit Price</th>
        <th>Total Price</th>
    </tr>

    <?php 
    $grand_total = 0;
    while($item = $items_res->fetch_assoc()): 
        $grand_total += $item['total_price'];
    ?>
    <tr>
        <td><?= h($item['Product_ID']) ?></td>
        <td><?= h($item['Product_Name']) ?></td>
        <td><?= h($item['quantity']) ?></td>
        <td>$<?= h(number_format($item['unit_price'],2)) ?></td>
        <td>$<?= h(number_format($item['total_price'],2)) ?></td>
    </tr>
    <?php endwhile; ?>

    <tr>
        <td colspan="4" style="text-align:right;"><strong>Grand Total:</strong></td>
        <td><strong>$<?= h(number_format($grand_total,2)) ?></strong></td>
    </tr>
</table>
<?php endif; ?>

<br>
<form action="view_all_orders.php" method="GET">
    <button type="submit">Back to Orders List</button>
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
