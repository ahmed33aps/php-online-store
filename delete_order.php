<?php
require_once __DIR__ . "/security.php";
require_once __DIR__ . "/db_connect.php";
if ($_SERVER['REQUEST_METHOD']!=='POST') fail_request('POST required.',405);
$order_id=(int)($_POST['order_id']??0);$conn->begin_transaction();
try {
 $order=one('SELECT * FROM orders WHERE Order_ID=? FOR UPDATE',[$order_id]);if(!$order)throw new RuntimeException();
 if(in_array($order['Status'],['Preparing','Prepared','Waiting'],true)) foreach(rows('SELECT Product_ID,quantity FROM order_items WHERE Order_ID=?',[$order_id]) as $item)sql('UPDATE products SET stock=stock+? WHERE Product_ID=?',[(int)$item['quantity'],(int)$item['Product_ID']]);
 sql('DELETE FROM orders WHERE Order_ID=?',[$order_id]);$conn->commit();$message='Order deleted.';$error=false;
}catch(Throwable $e){$conn->rollback();$message='Order could not be deleted.';$error=true;}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Order</title>
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
        .message {
            background: rgba(255, 255, 255, 0.1);  /* خلفية شفافة للرسائل */
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 600px;
            margin: 0 auto 30px;  /* مركز الرسائل أفقياً */
            backdrop-filter: blur(10px);  /* تأثير ضبابي */
            color: #4CAF50;  /* أخضر للنجاح */
            font-weight: bold;
        }
        .error {
            color: #ff6b6b;  /* أحمر للأخطاء */
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

<h2>Delete Order</h2>

<div class="message <?php if($error) echo 'error'; ?>">
    <?php echo $message; ?>
</div>

<!-- الزر كزر أنيق -->
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
