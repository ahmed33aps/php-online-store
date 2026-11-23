<?php
require_once __DIR__ . "/security.php";
require_once __DIR__ . "/db_connect.php";

// التأكد أن المستخدم admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: index.php");
    exit();
}

// جلب الـ product_id من GET
if (!isset($_GET['product_id'])) {
    echo "<p style='color:red;'>No product selected.</p>";
    echo '<a href="manage_products.php">Back to products</a>';
    exit();
}

$product_id = intval($_GET['product_id']);

// جلب بيانات المنتج الحالي
$product_res = $conn->query("SELECT * FROM products WHERE Product_ID=$product_id");
if ($product_res->num_rows == 0) {
    echo "<p style='color:red;'>Product not found.</p>";
    echo '<a href="manage_products.php">Back to products</a>';
    exit();
}

$product = $product_res->fetch_assoc();

// جلب جميع التصنيفات للاختيار
$categories_res = $conn->query("SELECT Category_Name FROM categories");

// معالجة التعديل
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_product'])) {
    $name = $conn->real_escape_string($_POST['product_name']);
    $desc = $conn->real_escape_string($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    if($price<0 || $stock<0 || !is_finite($price)) fail_request('Invalid product price or stock.');
    $category = $conn->real_escape_string($_POST['category']);

    $update_sql = "UPDATE products 
                   SET Product_Name='$name',
                       Description='$desc',
                       Current_Price=$price,
                       stock=$stock,
                       Category_Name='$category'
                   WHERE Product_ID=$product_id";

    if ($conn->query($update_sql)) {
        $message = "Product updated successfully!";
        // تحديث البيانات بعد الحفظ للعرض في الفورم
        $product = $conn->query("SELECT * FROM products WHERE Product_ID=$product_id")->fetch_assoc();
    } else {
        $message = "Error updating product: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
</head>
<body>

<h2>Edit Product</h2>
<?php if(isset($message)) echo "<p style='color:green;'>$message</p>"; ?>

<form method="POST">
    Name:<br>
    <input type="text" name="product_name" value="<?= h($product['Product_Name']) ?>" required><br><br>

    Description:<br>
    <textarea name="description" required><?= h($product['Description']) ?></textarea><br><br>

    Price:<br>
    <input type="number" name="price" step="0.01" value="<?= h($product['Current_Price']) ?>" required><br><br>

    Stock:<br>
    <input type="number" name="stock" value="<?= h($product['stock']) ?>" required><br><br>

    Category:<br>
    <select name="category" required>
        <?php while($cat = $categories_res->fetch_assoc()): ?>
            <option value="<?= h($cat['Category_Name']) ?>" <?= h($product['Category_Name']==$cat['Category_Name'] ? 'selected' : '') ?>>
                <?= h($cat['Category_Name']) ?>
            </option>
        <?php endwhile; ?>
    </select><br><br>

    <button type="submit" name="update_product">Update Product</button>
</form>

<br>
<a href="manage_products.php">Back to products</a>

</body>
</html>
