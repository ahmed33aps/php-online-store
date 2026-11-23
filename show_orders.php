<?php
require_once __DIR__ . "/security.php";
require_once __DIR__ . "/db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'customer') {
    header("Location: index.php");
    exit();
}

$customer_id = $_SESSION['user_id'];

// جلب كل الأوردرات الخاصة بالعميل
$orders = $conn->query("SELECT * FROM orders WHERE Customer_ID=$customer_id ORDER BY Order_Date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders</title>
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
        h3 {
            color: #e9ecef;
            font-size: 1.5em;
            margin-bottom: 10px;
            text-align: center;
        }
        h3 span {
            color: #ff6b6b;  /* أحمر للرسالة التحذيرية */
        }
        table {
            width: 100%;
            max-width: 800px;
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
        form {
            margin: 20px 0;
            display: inline-block;
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
            box-shadow: 0 8px 15px rgba(0,0,0,0.3);
            margin: 5px;
        }
        button:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 12px 20px rgba(0,0,0,0.4);
        }
        button[style*="background:red"] {
            background: linear-gradient(45deg, #e74c3c, #c0392b);  /* أحمر للـ Decline */
        }
        button[style*="background:red"]:hover {
            background: linear-gradient(45deg, #c0392b, #a93226);
        }
        hr {
            border: none;
            height: 2px;
            background: rgba(255, 255, 255, 0.3);
            margin: 40px 0;
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

<h2>My Orders</h2>

<?php while($order = $orders->fetch_assoc()): ?>
    <h3>
        Order ID: <?php echo h($order['Order_ID']); ?> | 
        Status: <?php echo h($order['Status']); ?> | 
        Date: <?php echo h($order['Order_Date']); ?>
        <?php if($order['Status'] == 'Waiting'): ?>
            <span>(Waiting for current Preparing order to finish)</span>
        <?php endif; ?>
    </h3>
    <table>
        <tr>
            <th>Product</th>
            <th>Price per unit</th>
            <th>Quantity</th>
            <th>Subtotal</th>
        </tr>

        <?php
        $items = $conn->query("SELECT oi.*, p.Product_Name, p.Current_Price 
                               FROM order_items oi 
                               JOIN products p ON oi.Product_ID=p.Product_ID
                               WHERE oi.Order_ID=".$order['Order_ID']);
        $total = 0;
        while($item = $items->fetch_assoc()):
            $subtotal = $item['Current_Price'] * $item['quantity'];
            $total += $subtotal;
        ?>
            <tr>
                <td><?php echo h($item['Product_Name']); ?></td>
                <td><?php echo h($item['Current_Price']); ?></td>
                <td><?php echo h($item['quantity']); ?></td>
                <td><?php echo h($subtotal); ?></td>
            </tr>
        <?php endwhile; ?>
        <tr>
            <td colspan="3"><b>Total</b></td>
            <td><b><?php echo h($total); ?></b></td>
        </tr>
    </table>

    <!-- زرار End Shopping للأوردرات Preparing فقط -->
    <?php if($order['Status'] == 'Preparing'): ?>
        <form method="POST" action="end_shopping.php">
            <input type="hidden" name="order_id" value="<?php echo h($order['Order_ID']); ?>">
            <button type="submit">End Shopping</button>
        </form>
    <?php endif; ?>

    <!-- زرار Reopen للأوردرات Prepared فقط -->
    <?php if($order['Status'] == 'Prepared'): ?>
        <form method="POST" action="reopen_order.php">
            <input type="hidden" name="order_id" value="<?php echo h($order['Order_ID']); ?>">
            <button type="submit">Reopen Order</button>
        </form>
    <?php endif; ?>

    <!-- زرار Decline يظهر فقط لو الأوردر مش Declined -->
    <?php if($order['Status'] != 'Declined'): ?>
    <form method="POST" action="decline_order.php">
        <input type="hidden" name="order_id" value="<?php echo h($order['Order_ID']); ?>">
        <button type="submit" style="background:red; color:white;">Decline Order</button>
    </form>
    <?php endif; ?>

    <hr>
<?php endwhile; ?>

<form action="customer_home.php" method="GET">
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
