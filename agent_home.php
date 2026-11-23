<?php
require_once __DIR__ . "/security.php";
require_once __DIR__ . "/db_connect.php";

// التأكد إن المستخدم agent
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'agent') {
    header("Location: login.php");
    exit();
}

$agent_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

if (isset($_POST['update_status'])) {
 $order_id=(int)($_POST['order_id']??0);$conn->begin_transaction();
 try {
  $order=one('SELECT * FROM orders WHERE Order_ID=? FOR UPDATE',[$order_id]);
  $next=['Prepared'=>'IN Delivery','IN Delivery'=>'Delivered'];
  if(!$order || !isset($next[$order['Status']])) throw new RuntimeException();
  $new=$next[$order['Status']];sql('UPDATE orders SET Status=? WHERE Order_ID=?',[$new,$order_id]);
  sql('INSERT INTO orderstatushistory (Old_Status,New_Status,Update_Date,Customer_ID,Agent_ID,Order_ID) VALUES (?,?,CURDATE(),?,?,?)',[$order['Status'],$new,(int)$order['Customer_ID'],(int)$agent_id,$order_id]);$conn->commit();
 } catch(Throwable $e){$conn->rollback();fail_request('Invalid order transition.');}
}
// جلب كل الأوردرات مع اسم العميل من جدول users
$orders_res = $conn->query("
    SELECT o.Order_ID, o.status, o.order_date, u.First_Name, u.Last_Name
    FROM orders o
    JOIN customers c ON o.Customer_ID = c.Customer_ID
    JOIN users u ON c.Customer_ID = u.User_ID
    ORDER BY o.order_date ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Agent Dashboard</title>
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
        h2, h3 {
            color: #ffd700;
            font-size: 2.5em;
            margin-bottom: 20px;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.7);
            text-align: center;  /* تأكيد المركز */
        }
        h3 {
            font-size: 1.8em;
            margin-top: 40px;
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
        button {
            padding: 10px 20px;
            background: linear-gradient(45deg, #ff6b6b, #feca57);
            color: white;
            border: none;
            border-radius: 20px;
            font-size: 1em;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.4s ease;
            box-shadow: 0 5px 10px rgba(0,0,0,0.3);
            margin: 5px;
        }
        button:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 15px rgba(0,0,0,0.4);
        }
        .logout-form {
            margin-top: 30px;
        }
        .logout-form button {
            width: 200px;  /* عرض ثابت للزر السفلي */
            background: linear-gradient(45deg, #e74c3c, #c0392b);  /* أحمر للخروج */
        }
        .logout-form button:hover {
            background: linear-gradient(45deg, #c0392b, #a93226);
        }
    
/* Compact inline actions, keeping full-page forms unchanged. */
td form {display:inline-block;margin:0;padding:0;background:none;box-shadow:none;border:0;width:auto;max-width:none;}
td form button {margin:4px;}
</style>
</head>
<body>

<h2>Welcome Agent: <?= h(htmlspecialchars($username)) ?></h2>

<h3>Orders</h3>
<table>
    <tr>
        <th>Order ID</th>
        <th>Customer</th>
        <th>Order Date</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
    <?php while($order = $orders_res->fetch_assoc()): ?>
        <tr>
            <td><?= h($order['Order_ID']) ?></td>
            <td><?= h(htmlspecialchars($order['First_Name'] . ' ' . $order['Last_Name'])) ?></td>
            <td><?= h($order['order_date']) ?></td>
            <td><?= h($order['status']) ?></td>
            <td>
                <?php if($order['status'] != 'Delivered'): ?>
                    <form method="POST" style="margin:0;">
                        <input type="hidden" name="order_id" value="<?= h($order['Order_ID']) ?>">
                        <input type="hidden" name="current_status" value="<?= h($order['status']) ?>">
                        <button type="submit" name="update_status">
                            <?php
                                if($order['status']=='Prepared') echo 'Mark as IN Delivery';
                                elseif($order['status']=='IN Delivery') echo 'Mark as Delivered';
                            ?>
                        </button>
                    </form>
                <?php else: ?>
                    Completed
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

<form method="POST" action="logout.php" class="logout-form">
    <button type="submit">Logout</button>
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
