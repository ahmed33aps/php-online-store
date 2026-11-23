<?php
require_once __DIR__ . "/security.php";
require_once __DIR__ . "/db_connect.php";

// التأكد أن المستخدم admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: index.php");
    exit();
}

// إضافة فئة جديدة
if (isset($_POST['add'])) {
    $category_name = $_POST['category_name'];
    $description = $_POST['description'];
    $admin_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO categories (Admin_ID, Category_Name, Description) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $admin_id, $category_name, $description);

    if ($stmt->execute()) {
        $message = "Category added successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}

// تعديل فئة موجودة
if (isset($_POST['edit'])) {
    $old_name = $_POST['old_name'];
    $new_name = $_POST['category_name'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("UPDATE categories SET Category_Name=?, Description=? WHERE Category_Name=?");
    $stmt->bind_param("sss", $new_name, $description, $old_name);

    if ($stmt->execute()) {
        $message = "Category updated successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}

// حذف فئة
if (isset($_POST['delete'])) {
    $category_name = $_POST['category_name'];

    // 1) التحقق إذا فيه منتجات مرتبطة بالفئة
    $check = $conn->prepare("SELECT COUNT(*) as cnt FROM products WHERE Category_Name=?");
    $check->bind_param("s", $category_name);
    $check->execute();
    $check->bind_result($count);
    $check->fetch();
    $check->close();

    if ($count > 0) {
        // لو فيه منتجات، ماتمسحش
        $message = "Cannot delete this category! There are $count product(s) associated with it.";
    } else {
        // لو مفيش منتجات، نحذف الفئة
        $stmt = $conn->prepare("DELETE FROM categories WHERE Category_Name=?");
        $stmt->bind_param("s", $category_name);

        if ($stmt->execute()) {
            $message = "Category deleted successfully!";
        } else {
            $message = "Error: " . $conn->error;
        }
    }
}

// جلب كل الفئات
$result = $conn->query("SELECT * FROM categories ORDER BY Category_Name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage categories</title>
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
        input, textarea {
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
        input:focus, textarea:focus {
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

<h2>Manage categories</h2>

<?php if(isset($message)) echo "<div class='message'>$message</div>"; ?>

<!-- إضافة فئة جديدة -->
<h3>Add New Category</h3>
<form method="POST">
    <label for="category_name">Category Name:</label>
    <input type="text" name="category_name" required>

    <label for="description">Description:</label>
    <textarea name="description" required></textarea>

    <button type="submit" name="add">Add Category</button>
</form>

<hr>

<!-- عرض وتعديل وحذف الفئات -->
<h3>All categories</h3>
<table>
    <tr>
        <th>Category Name</th>
        <th>Description</th>
        <th>Actions</th>
    </tr>
    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
        <td class="category-name"><?= h($row['Category_Name']) ?></td>
        <td class="description"><?= h($row['Description']) ?></td>
        <td class="actions">
            <button class="edit-btn" onclick="toggleEdit(this)">Edit</button>
            <form action="" method="POST" style="display:inline;">
                <input type="hidden" name="category_name" value="<?= h($row['Category_Name']) ?>">
                <button type="submit" name="delete" onclick="return confirm('Are you sure?')">Delete</button>
            </form>
            <!-- نموذج التعديل مخفي -->
            <div class="edit-form">
                <form action="" method="POST">
                    <input type="hidden" name="old_name" value="<?= h($row['Category_Name']) ?>">
                    <label>Category Name:</label>
                    <input type="text" name="category_name" value="<?= h($row['Category_Name']) ?>" required>
                    <label>Description:</label>
                    <input type="text" name="description" value="<?= h($row['Description']) ?>" required>
                    <button type="submit" name="edit">Save Edit</button>
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
