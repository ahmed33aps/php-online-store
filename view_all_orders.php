<?php
require_once __DIR__ . "/security.php";
require_once __DIR__ . "/db_connect.php";

// التأكد أن المستخدم admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: index.php");
    exit();
}

// جلب كل الأوردرات مع اسم العميل وعدد المنتجات والإجمالي
$sql = "
SELECT o.Order_ID, o.Order_Date, o.Status, u.First_Name, u.Last_Name,
       COUNT(oi.Product_ID) AS total_products,
       IFNULL(SUM(oi.total_price),0) AS order_total
FROM orders o
JOIN users u ON o.Customer_ID = u.User_ID
LEFT JOIN order_items oi ON o.Order_ID = oi.Order_ID
GROUP BY o.Order_ID
ORDER BY o.Order_Date DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Orders</title>
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
        table {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
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
        form {
            display: inline-block;
            margin: 0 5px;
        }
        button {
            padding: 8px 15px;
            background: linear-gradient(45deg, #ff6b6b, #feca57);
            color: white;
            border: none;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.4s ease;
            box-shadow: 0 5px 10px rgba(0,0,0,0.3);
        }
        button:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 15px rgba(0,0,0,0.4);
        }
        button[onclick] {
            background: linear-gradient(45deg, #e74c3c, #c0392b);  /* أحمر للحذف */
        }
        button[onclick]:hover {
            background: linear-gradient(45deg, #c0392b, #a93226);
        }
        .back-form {
            margin-top: 30px;
        }
        .back-form button {
            width: 200px;  /* عرض ثابت للزر السفلي */
        }
    
/* Compact inline actions, keeping full-page forms unchanged. */
td form {display:inline-block;margin:0;padding:0;background:none;box-shadow:none;border:0;width:auto;max-width:none;}
td form button {margin:4px;}
</style>
</head>
<body>

<h2>All Orders</h2>

<table>
    <tr>
        <th>Order ID</th>
        <th>Customer</th>
        <th>Order Date</th>
        <th>Status</th>
        <th>Total products</th>
        <th>Total Price</th>
        <th>Actions</th>
    </tr>

    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= h($row['Order_ID']) ?></td>
        <td><?= h($row['First_Name'] . " " . $row['Last_Name']) ?></td>
        <td><?= h($row['Order_Date']) ?></td>
        <td><?= h($row['Status']) ?></td>
        <td><?= h($row['total_products']) ?></td>
        <td>$<?= h(number_format($row['order_total'],2)) ?></td>
        <td>
            <form action="view_order_details.php" method="GET">
                <input type="hidden" name="order_id" value="<?= h($row['Order_ID']) ?>">
                <button type="submit">View Details</button>
            </form>
            <!-- زر حذف الأوردر -->
            <form action="delete_order.php" method="POST">
                <input type="hidden" name="order_id" value="<?= h($row['Order_ID']) ?>">
                <button type="submit" onclick="return confirm('Are you sure you want to delete this order?')">Delete</button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<br>
<form action="admin_home.php" method="GET" class="back-form">
    <button type="submit">Back to Home</button>
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
