<?php
require_once __DIR__ . "/security.php";
require_once __DIR__ . "/db_connect.php";

// التأكد أن المستخدم admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: index.php");
    exit();
}

// جلب كل العملاء
$sql = "SELECT u.User_ID, u.First_Name, u.Last_Name, u.Email, u.Sex, c.points
        FROM users u
        JOIN customers c ON u.User_ID = c.Customer_ID
        ORDER BY u.First_Name, u.Last_Name";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Customers</title>
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
            max-width: 1000px;
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

<h2>All Customers</h2>

<table>
    <tr>
        <th>User ID</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Email</th>
        <th>Sex</th>
        <th>Points</th>
        <th>Actions</th>
    </tr>

    <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= h($row['User_ID']) ?></td>
            <td><?= h($row['First_Name']) ?></td>
            <td><?= h($row['Last_Name']) ?></td>
            <td><?= h($row['Email']) ?></td>
            <td><?= h($row['Sex']) ?></td>
            <td><?= h($row['points']) ?></td>
            <td>
                <!-- أزرار تعديل أو حذف العميل -->
                <form action="edit_customer.php" method="GET">
                    <input type="hidden" name="user_id" value="<?= h($row['User_ID']) ?>">
                    <button type="submit">Edit</button>
                </form>
                <form action="delete_customer.php" method="POST">
                    <input type="hidden" name="user_id" value="<?= h($row['User_ID']) ?>">
                    <button type="submit" onclick="return confirm('Are you sure?')">Delete</button>
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
