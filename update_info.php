<?php
require_once __DIR__ . "/security.php";
require_once __DIR__ . "/db_connect.php";

// --- التحقق من تسجيل الدخول ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// --- جلب بيانات المستخدم من الجدولين ---
$user = $conn->query("SELECT * FROM users WHERE User_ID=$user_id")->fetch_assoc();

// جلب بيانات الجدول الفرعي حسب النوع
if ($user_type == "customer") {
    $extra = $conn->query("SELECT * FROM customers WHERE Customer_ID=$user_id")->fetch_assoc();
}
elseif ($user_type == "agent") {
    $extra = $conn->query("SELECT * FROM agents WHERE Agent_ID=$user_id")->fetch_assoc();
}
elseif ($user_type == "admin") {
    $extra = $conn->query("SELECT * FROM administrators WHERE Admin_ID=$user_id")->fetch_assoc();
}

// --- عند ضغط Update ---
if (isset($_POST["update"])) {

    // 1) تحديث بيانات جدول users (باستخدام prepared statements عشان الأمان)
    $u_name   = $_POST["username"];
    $ssn      = $_POST["ssn"];
    $first    = $_POST["first_name"];
    $last     = $_POST["last_name"];
    $sex      = $_POST["sex"];
    $email    = $_POST["email"];
    $password_plain = $_POST["password"];
    $password = $password_plain !== '' ? password_hash($password_plain, PASSWORD_DEFAULT) : $user['password'];

    $stmt = $conn->prepare("
        UPDATE users SET
            User_Name=?,
            First_Name=?,
            Last_Name=?,
            Sex=?,
            Email=?,
            password=?
        WHERE User_ID=?
    ");
    $stmt->bind_param("ssssssi", $u_name, $first, $last, $sex, $email, $password, $user_id);
    $stmt->execute();

    // 2) تحديث الجدول الفرعي حسب النوع
    if ($user_type == "customer") {
        // points ثابتة مش هتتعدل
    }
    elseif ($user_type == "agent") {
        $office = $_POST["office"];
        $stmt2 = $conn->prepare("UPDATE agents SET Office=? WHERE Agent_ID=?");
        $stmt2->bind_param("si", $office, $user_id);
        $stmt2->execute();
    }
    elseif ($user_type == "admin") {
        // مفيش حاجه للأدمن
    }

    $message = "Updated Successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Info</title>
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
        h4 {
            color: #e9ecef;
            font-size: 1.5em;
            margin-top: 20px;
        }
        .message {
            background: rgba(255, 255, 255, 0.1);  /* خلفية شفافة للرسائل */
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 500px;
            margin: 0 auto 20px;  /* مركز الرسائل أفقياً */
            backdrop-filter: blur(10px);  /* تأثير ضبابي */
            color: #4CAF50;  /* أخضر للنجاح */
            font-weight: bold;
        }
        form {
            background: rgba(255, 255, 255, 0.1);  /* خلفية شفافة للنموذج */
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 500px;
            margin: 0 auto;  /* مركز النموذج أفقياً */
            backdrop-filter: blur(10px);  /* تأثير ضبابي */
            text-align: left;  /* النصوص داخل النموذج على اليسار عشان الـ labels */
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #ffd700;
        }
        input, select {
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
        input:focus, select:focus {
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
        .back-button {
            margin-top: 20px;
            display: inline-block;
        }
        .back-button button {
            width: 200px;  /* عرض أصغر للزر السفلي */
        }
    
/* Compact inline actions, keeping full-page forms unchanged. */
td form {display:inline-block;margin:0;padding:0;background:none;box-shadow:none;border:0;width:auto;max-width:none;}
td form button {margin:4px;}
</style>
    <script>
        // تحسين JS: التحقق من الحقول قبل الإرسال
        function validateForm() {
            let username = document.querySelector('input[name="username"]').value.trim();
            let email = document.querySelector('input[name="email"]').value;
            let password = document.querySelector('input[name="password"]').value;

            if (username.length < 3) {
                alert('Username must be at least 3 characters!');
                return false;
            }
            if (!email.includes('@')) {
                alert('Please enter a valid email!');
                return false;
            }
            if (password.length < 6) {
                alert('Password must be at least 6 characters!');
                return false;
            }
            return true;
        }
    </script>
</head>
<body>

<h2>Update Your Information</h2>

<?php if(isset($message)) echo "<div class='message'>$message</div>"; ?>

<form method="POST" onsubmit="return validateForm()">

    <label for="username">Username:</label>
    <input type="text" name="username" value="<?= h($user['User_Name']) ?>" required>

    " required>

    <label for="first_name">First Name:</label>
    <input type="text" name="first_name" value="<?= h($user['First_Name']) ?>" required>

    <label for="last_name">Last Name:</label>
    <input type="text" name="last_name" value="<?= h($user['Last_Name']) ?>" required>

    <label for="sex">Sex:</label>
    <select name="sex">
        <option value="Male"   <?= h(($user['Sex']=="Male")?"selected":"") ?>>Male</option>
        <option value="Female" <?= h(($user['Sex']=="Female")?"selected":"") ?>>Female</option>
        <option value="Other"  <?= h(($user['Sex']=="Other")?"selected":"") ?>>Other</option>
    </select>

    <label for="email">Email:</label>
    <input type="email" name="email" value="<?= h($user['Email']) ?>" required>

    <label for="password">Password:</label>
    <input type="password" name="password" autocomplete="new-password">" required>

    <!-- Customer -->
    <?php if ($user_type == "customer"): ?>
        <h4>Customer Info</h4>
        <p>Points: <?= h($extra['points']) ?> (cannot be edited)</p>
    <?php endif; ?>

    <!-- Agent -->
    <?php if ($user_type == "agent"): ?>
        <h4>Agent Info</h4>
        <label for="office">Office Number:</label>
        <input type="number" name="office" value="<?= h($extra['Office']) ?>">
    <?php endif; ?>

    <!-- Admin -->
    <?php if ($user_type == "admin"): ?>
        <h4>Admin Info</h4>
        <p>No extra fields.</p>
    <?php endif; ?>

    <button type="submit" name="update">Update Info</button>
</form>

<!-- الزر السفلي كزر أنيق -->
<div class="back-button">
    <form action="index.php" method="GET">
        <button type="submit">Back</button>
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
