<?php
require_once __DIR__ . "/security.php";
require_once __DIR__ . "/db_connect.php";

// التأكد أن المستخدم admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: index.php");
    exit();
}

// إضافة وكيل جديد (Customer → Agent)
if (isset($_POST['add_agent'])) {

    $user_id = intval($_POST['user_id']);
    $office  = $_POST['office'];

    // هل هو Agent بالفعل؟
    $check = $conn->query("SELECT * FROM agents WHERE Agent_ID = $user_id");

    if ($check->num_rows == 0) {

        $stmt = $conn->prepare("INSERT INTO agents (Agent_ID, Office) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $office);
        $stmt->execute();

        $message = "Agent added successfully. Customer order history was preserved.";

    } else {
        $message = "This user is already an agent.";
    }
}

if (isset($_POST['delete_agent'])) {

    $agent_id = intval($_POST['agent_id']);

    // 1) حذف orderstatushistory اللي كان الوكيل ماسكها
    sql("UPDATE orderstatushistory SET Agent_ID=NULL WHERE Agent_ID=?",[$agent_id]);

    // 2) حذف الوكيل
    $stmt = $conn->prepare("DELETE FROM agents WHERE Agent_ID=?");
    $stmt->bind_param("i", $agent_id);
    $stmt->execute();

    // 3) إضافته كـ Customer (points = 0) بدون Duplicate Error
    $conn->query("INSERT INTO customers (Customer_ID, points) VALUES ($agent_id, 0)
                ON DUPLICATE KEY UPDATE points = 0");

    $message = "Agent deleted and reverted to Customer successfully!";
}

// تعديل وكيل
if (isset($_POST['update_agent'])) {
    $agent_id = intval($_POST['agent_id']);
    $office = $_POST['office'];
    $stmt = $conn->prepare("UPDATE agents SET Office=? WHERE Agent_ID=?");
    $stmt->bind_param("si", $office, $agent_id);
    $stmt->execute();
    $message = "Agent updated successfully!";
}

// جلب كل الوكلاء الحاليين
$agents_res = $conn->query("
    SELECT a.Agent_ID, a.Office, u.First_Name, u.Last_Name
    FROM agents a
    JOIN users u ON a.Agent_ID = u.User_ID
");

// جلب كل المستخدمين اللي ممكن يتحولوا لوكلاء (مش موجودين في جدول agents)
$users_res = $conn->query("
    SELECT u.User_ID, u.First_Name, u.Last_Name
    FROM users u
    WHERE u.User_ID NOT IN (SELECT Agent_ID FROM agents)
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Agents</title>
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

<h2>Manage Agents</h2>

<?php if(isset($message)) echo "<div class='message'>$message</div>"; ?>

<h3>Add New Agent</h3>
<form method="POST">
    <label for="user_id_input">Select User:</label>
    <input list="users_list" name="user_id" id="user_id_input" placeholder="Type name to search..." required>
    <datalist id="users_list">
        <?php 
        $users_res = $conn->query("
            SELECT u.User_ID, u.First_Name, u.Last_Name
            FROM users u
            JOIN customers c ON u.User_ID = c.Customer_ID
        ");
        while($user = $users_res->fetch_assoc()): ?>
            <option value="<?= h($user['User_ID']) ?>"><?= h($user['First_Name']) ?> <?= h($user['Last_Name']) ?></option>
        <?php endwhile; ?>
    </datalist>

    <label for="office">Office Number:</label>
    <input type="text" name="office" required>

    <button type="submit" name="add_agent">Add Agent</button>
</form>

<h3>Existing Agents</h3>
<table>
    <tr>
        <th>Agent ID</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Office</th>
        <th>Actions</th>
    </tr>
    <?php while($agent = $agents_res->fetch_assoc()): ?>
        <tr>
            <form method="POST">
                <td><?= h($agent['Agent_ID']) ?></td>
                <td><?= h($agent['First_Name']) ?></td>
                <td><?= h($agent['Last_Name']) ?></td>
                <td>
                    <input type="text" name="office" value="<?= h($agent['Office']) ?>" required>
                </td>
                <td class="actions">
                    <input type="hidden" name="agent_id" value="<?= h($agent['Agent_ID']) ?>">
                    <button type="submit" name="update_agent">Update</button>
                    <button type="submit" name="delete_agent" onclick="return confirm('Are you sure?')">Delete</button>
                </td>
            </form>
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
