<?php
require_once __DIR__ . "/security.php";
require_once __DIR__ . "/db_connect.php";

// التأكد أن المستخدم admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: index.php");
    exit();
}

// ==================== Add Product ====================
if (isset($_POST['add_product'])) {
    $name = $_POST['product_name'];
    $desc = $_POST['description'];
    $price = floatval($_POST['current_price']);
    $stock = intval($_POST['stock']);
    $category = $_POST['category_name'];
    $admin_id = $_SESSION['user_id'];
    if($price<0 || $stock<0 || !is_finite($price)) fail_request('Invalid product price or stock.');

    $stmt = $conn->prepare("INSERT INTO products (Product_Name, Description, Current_Price, Stock, Category_Name, Admin_ID)
            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdisi", $name, $desc, $price, $stock, $category, $admin_id);

    if ($stmt->execute()) {
        $message = "Product added successfully!";
    } else {
        $error = "Error: " . $conn->error;
    }
}

// ==================== Delete Product ====================
if (isset($_POST['delete_product'])) {
    $product_id = intval($_POST['product_id']);

    // 1) حذف أي item مرتبط بالمنتج في Order_Items
    if(one('SELECT Product_ID FROM order_items WHERE Product_ID=? LIMIT 1',[$product_id])) fail_request('Product is referenced by an order and cannot be deleted.');

    // 2) حذف المنتج نفسه
    $stmt = $conn->prepare("DELETE FROM products WHERE Product_ID=?");
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        $message = "Product deleted successfully!";
    } else {
        $error = "Error deleting product: " . $conn->error;
    }
}

// ==================== Fetch all products ====================
$products_res = $conn->query("SELECT p.*, c.Category_Name 
                              FROM products p
                              JOIN categories c ON p.Category_Name = c.Category_Name
                              ORDER BY p.Product_ID");

// ==================== Fetch all categories for select ====================
$categories_res = $conn->query("SELECT Category_Name FROM categories");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage products</title>
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
        .message {
            background: rgba(255, 255, 255, 0.1);  /* خلفية شفافة للرسائل */
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 600px;
            margin: 0 auto 20px;  /* مركز الرسائل أفقياً */
            backdrop-filter: blur(10px);  /* تأثير ضبابي */
            color: #4CAF50;  /* أخضر للنجاح */
            font-weight: bold;
        }
        .error {
            color: #ff6b6b;  /* أحمر للأخطاء */
        }
        form {
            background: rgba(255, 255, 255, 0.1);  /* خلفية شفافة للنموذج */
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 500px;
            margin: 0 auto 20px;  /* مركز النموذج أفقياً */
            backdrop-filter: blur(10px);  /* تأثير ضبابي */
            text-align: left;  /* النصوص داخل النموذج على اليسار عشان الـ labels */
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #ffd700;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: none;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.2);
            color: #333;
            font-size: 1em;
            box-sizing: border-box;  /* عشان الـ width يشمل الـ padding */
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            box-shadow: 0 0 10px #ffd700;
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
        button[onclick] {
            background: linear-gradient(45deg, #e74c3c, #c0392b);  /* أحمر للحذف */
        }
        button[onclick]:hover {
            background: linear-gradient(45deg, #c0392b, #a93226);
        }
        table {
            width: 100%;
            max-width: 1200px;
            margin: 20px auto;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            backdrop-filter: blur(10px);
        }
        th, td {
            padding: 10px;
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
        .actions {
            display: flex;
            justify-content: center;
            gap: 10px;  /* مسافة بين الأزرار */
            flex-wrap: wrap;  /* عشان ينزلوا تحت بعض على الموبايل */
        }
        .edit-form {
            display: none;  /* مخفي افتراضياً */
            margin-top: 10px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            text-align: left;
        }
        .edit-form input {
            width: 100%;
            margin-bottom: 10px;
        }
        hr {
            border: none;
            height: 2px;
            background: rgba(255, 255, 255, 0.3);
            margin: 40px 0;
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

<h2>Manage products</h2>

<?php if(isset($message)) echo "<div class='message'>$message</div>"; ?>
<?php if(isset($error)) echo "<div class='message error'>$error</div>"; ?>

<!-- ===== Add Product Form ===== -->
<h3>Add New Product</h3>
<form method="POST">
    <label for="product_name">Name:</label>
    <input type="text" name="product_name" required>

    <label for="description">Description:</label>
    <textarea name="description" required></textarea>

    <label for="current_price">Price:</label>
    <input type="number" step="0.01" name="current_price" required>

    <label for="stock">Stock:</label>
    <input type="number" name="stock" required>

    <label for="category_name">Category:</label>
    <select name="category_name" required>
        <?php while($cat = $categories_res->fetch_assoc()): ?>
            <option value="<?= h($cat['Category_Name']) ?>"><?= h($cat['Category_Name']) ?></option>
        <?php endwhile; ?>
    </select>

    <button type="submit" name="add_product">Add Product</button>
</form>

<hr>

<!-- ===== products Table ===== -->
<h3>All products</h3>
<table>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Description</th>
        <th>Price</th>
        <th>Stock</th>
        <th>Category</th>
        <th>Actions</th>
    </tr>
    <?php while($prod = $products_res->fetch_assoc()): ?>
        <tr>
            <td class="product-id"><?= h($prod['Product_ID']) ?></td>
            <td class="product-name"><?= h($prod['Product_Name']) ?></td>
            <td class="description"><?= h($prod['Description']) ?></td>
            <td class="price"><?= h($prod['Current_Price']) ?></td>
            <td class="stock"><?= h($prod['stock']) ?></td>
            <td class="category"><?= h($prod['Category_Name']) ?></td>
            <td class="actions">
                <button class="edit-btn" onclick="toggleEdit(this)">Edit</button>
                <form action="" method="POST" style="display:inline;">
                    <input type="hidden" name="product_id" value="<?= h($prod['Product_ID']) ?>">
                    <button type="submit" name="delete_product" onclick="return confirm('Are you sure?')">Delete</button>
                </form>
                <!-- نموذج التعديل مخفي -->
                <div class="edit-form">
                    <form action="edit_product.php" method="POST">
                        <input type="hidden" name="product_id" value="<?= h($prod['Product_ID']) ?>">
                        <label>Name:</label>
                        <input type="text" name="product_name" value="<?= h($prod['Product_Name']) ?>" required>
                        <label>Description:</label>
                        <input type="text" name="description" value="<?= h($prod['Description']) ?>" required>
                        <label>Price:</label>
                        <input type="number" step="0.01" name="current_price" value="<?= h($prod['Current_Price']) ?>" required>
                        <label>Stock:</label>
                        <input type="number" name="stock" value="<?= h($prod['stock']) ?>" required>
                        <label>Category:</label>
                        <select name="category_name" required>
                            <?php 
                            $categories_res2 = $conn->query("SELECT Category_Name FROM categories");
                            while($cat = $categories_res2->fetch_assoc()): ?>
                                <option value="<?= h($cat['Category_Name']) ?>" <?= h(($cat['Category_Name'] == $prod['Category_Name']) ? 'selected' : '') ?>><?= h($cat['Category_Name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                        <button type="submit" name="edit_product">Save Edit</button>
                        <button type="button" onclick="cancelEdit(this)">Cancel</button>
                    </form>
                </div>
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

    // دالة لتغيير وضع التعديل
    function toggleEdit(btn) {
        const actionsCell = btn.parentElement;
        const editForm = actionsCell.querySelector('.edit-form');
        if (editForm.style.display === 'block') {
            editForm.style.display = 'none';
        } else {
            editForm.style.display = 'block';
        }
    }

    // دالة لإلغاء التعديل
    function cancelEdit(btn) {
        const editForm = btn.parentElement;
        editForm.style.display = 'none';
    }
</script>

</body>
</html>
