<?php
require_once __DIR__ . "/security.php";
require_once __DIR__ . "/db_connect.php";
$customer_id=(int)$_SESSION['user_id'];$order_id=(int)($_POST['order_id']??0);
if ($_SERVER['REQUEST_METHOD']!=='POST') fail_request('POST required.',405);
$conn->begin_transaction();
try {
 sql('SELECT Customer_ID FROM customers WHERE Customer_ID=? FOR UPDATE',[$customer_id]);
 $order=one("SELECT * FROM orders WHERE Order_ID=? AND Customer_ID=? AND Status='Prepared' FOR UPDATE",[$order_id,$customer_id]);
 if(!$order) throw new RuntimeException();
 $current=one("SELECT Order_ID FROM orders WHERE Customer_ID=? AND Status='Preparing'",[$customer_id]);
 if($current) {sql("UPDATE orders SET Status='Waiting' WHERE Order_ID=?",[(int)$current['Order_ID']]);sql("INSERT INTO orderstatushistory (Old_Status,New_Status,Update_Date,Customer_ID,Order_ID) VALUES ('Preparing','Waiting',CURDATE(),?,?)",[$customer_id,(int)$current['Order_ID']]);}
 sql("UPDATE orders SET Status='Preparing' WHERE Order_ID=?",[$order_id]);sql("INSERT INTO orderstatushistory (Old_Status,New_Status,Update_Date,Customer_ID,Order_ID) VALUES ('Prepared','Preparing',CURDATE(),?,?)",[$customer_id,$order_id]);$conn->commit();$message='<p>Order reopened.</p>';
}catch(Throwable $e){$conn->rollback();fail_request('Order cannot be reopened.');}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reopen Order</title>
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

<h1>Reopen Order</h1>

<div class="message">
    <?php echo $message; ?>
</div>

<!-- الزر كزر أنيق -->
<form action="show_orders.php" method="GET">
    <button type="submit">Back to My Orders</button>
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
