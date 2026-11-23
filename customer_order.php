<?php
require_once __DIR__ . "/security.php";
require_once __DIR__ . "/db_connect.php";
$customer_id=(int)$_SESSION['user_id'];
$conn->begin_transaction();
try {
 sql('SELECT Customer_ID FROM customers WHERE Customer_ID=? FOR UPDATE',[$customer_id]);
 $order=one("SELECT * FROM orders WHERE Customer_ID=? AND Status='Preparing' FOR UPDATE",[$customer_id]);
 if (!$order) {sql("INSERT INTO orders (Order_Date,Status,Customer_ID) VALUES (CURDATE(),'Preparing',?)",[$customer_id]);$order_id=$conn->insert_id;} else $order_id=(int)$order['Order_ID'];
 if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['product_id'],$_POST['quantity'])) {
  $pid=filter_var($_POST['product_id'],FILTER_VALIDATE_INT);$qty=filter_var($_POST['quantity'],FILTER_VALIDATE_INT);
  if (!$pid || !$qty || $qty<1 || $qty>10000) throw new RuntimeException('Invalid quantity.');
  $product=one('SELECT * FROM products WHERE Product_ID=? FOR UPDATE',[$pid]);
  if (!$product || (int)$product['stock']<$qty) throw new RuntimeException('Product unavailable or not enough stock.');
  sql('INSERT INTO order_items (Order_ID,Product_ID,unit_price,quantity,total_price) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE quantity=quantity+VALUES(quantity),total_price=total_price+VALUES(total_price)',[$order_id,$pid,(float)$product['Current_Price'],$qty,$qty*(float)$product['Current_Price']]);
  sql('UPDATE products SET stock=stock-? WHERE Product_ID=?',[$qty,$pid]);
 }
 $conn->commit();
} catch(Throwable $e) {$conn->rollback();fail_request('Unable to update order. Check product and positive quantity.');}
$products_res=rows('SELECT * FROM products');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product to Order</title>
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
            margin: 20px 0;
        }
        p[style*="color:red"] {
            color: #ff6b6b !important;  /* أحمر للأخطاء */
        }
        p[style*="color:green"] {
            color: #4CAF50 !important;  /* أخضر للنجاح */
        }
        form {
            background: rgba(255, 255, 255, 0.1);  /* خلفية شفافة للنموذج */
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 500px;
            margin: 0 auto 20px;  /* مركز النموذج أفقياً مع مسافة تحت */
            backdrop-filter: blur(10px);  /* تأثير ضبابي */
            text-align: left;  /* النصوص داخل النموذج على اليسار عشان الـ labels */
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #ffd700;
        }
        select, input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: none;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.2);
            color: #333;
            font-size: 1em;
            box-sizing: border-box;  /* عشان الـ width يشمل الـ padding */
        }
        select:focus, input:focus {
            outline: none;
            box-shadow: 0 0 10px #ffd700;
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
        .button-group button {
            width: 200px;  /* عرض ثابت للأزرار السفلية */
        }
    
/* Compact inline actions, keeping full-page forms unchanged. */
td form {display:inline-block;margin:0;padding:0;background:none;box-shadow:none;border:0;width:auto;max-width:none;}
td form button {margin:4px;}
</style>
    <script>
        // تحسين JS: التحقق من الحقول قبل الإرسال
        function validateForm() {
            let productId = document.querySelector('select[name="product_id"]').value;
            let quantity = document.querySelector('input[name="quantity"]').value;

            if (!productId) {
                alert('Please select a product!');
                return false;
            }
            if (quantity < 1) {
                alert('Quantity must be at least 1!');
                return false;
            }
            return true;
        }
    </script>
</head>
<body>

<h2>Add Product to Order</h2>

<form method="POST" onsubmit="return validateForm()">
    <label>Select Product:</label>
    <select name="product_id" required>
        <?php while($prod = $products_res->fetch_assoc()): ?>
            <option value="<?= h($prod['Product_ID']) ?>">
                <?= h($prod['Product_Name']) ?> - Price: <?= h($prod['Current_Price']) ?> - Stock: <?= h($prod['stock']) ?>
            </option>
        <?php endwhile; ?>
    </select><br>

    <label>Quantity:</label>
    <input type="number" name="quantity" min="1" required>

    <button type="submit">Add Product</button>
</form>

<!-- الأزرار السفلية كأزرار أنيقة -->
<div class="button-group">
    <form action="show_orders.php" method="GET">
        <button type="submit">Show My Orders</button>
    </form>
    <form action="customer_home.php" method="GET">
        <button type="submit">Go to Home</button>
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
