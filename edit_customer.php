<?php
require_once __DIR__ . "/security.php";
require_once __DIR__ . "/db_connect.php";

// التأكد أن المستخدم admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: index.php");
    exit();
}

// التأكد من وجود user_id في GET
if (!isset($_GET['user_id'])) {
    echo "No customer selected.";
    exit();
}

$customer_id = intval($_GET['user_id']);

$message = "";
$error = "";

// معالجة التحديث
if (isset($_POST['update'])) {
    $first = $_POST['first_name'];
    $last = $_POST['last_name'];
    $email = $_POST['email'];
    $sex = $_POST['sex'];
    $points = intval($_POST['points']);

    // باسوورد جديد (لو المستخدم كتب حاجة)
    $new_pass_plain = trim($_POST['password_plain']);
    $new_pass_confirm = trim($_POST['password_confirm']);

    // تحديث جدول users
    $stmt = $conn->prepare("UPDATE users 
                  SET First_Name=?, 
                      Last_Name=?, 
                      Email=?, 
                      Sex=?
                  WHERE User_ID=?");
    $stmt->bind_param("ssssi", $first, $last, $email, $sex, $customer_id);
    $stmt->execute();

    // تحديث جدول customers
    $stmt2 = $conn->prepare("UPDATE customers SET points=? WHERE Customer_ID=?");
    $stmt2->bind_param("ii", $points, $customer_id);
    $stmt2->execute();

    // لو الباسورد مش فاضي → ابدأ في التغيير
    if ($new_pass_plain !== "") {

        if ($new_pass_plain === $new_pass_confirm) {

            // تشفير الباسورد
            $hashed = password_hash($new_pass_plain, PASSWORD_DEFAULT);

            // تحديث الباسورد في users
            $stmt3 = $conn->prepare("UPDATE users 
                          SET password=?
                          WHERE User_ID=?");
            $stmt3->bind_param("si", $hashed, $customer_id);
            $stmt3->execute();

            $message = "Customer info and password updated successfully!";

        } else {
            $error = "Passwords do NOT match!";
        }

    } else {
        $message = "Customer info updated successfully!";
    }
}

// جلب بيانات العميل لعرضها في الفورم
$user = $conn->query("SELECT u.User_ID, u.First_Name, u.Last_Name, u.Email, u.Sex, c.points
                      FROM users u
                      JOIN customers c ON u.User_ID = c.Customer_ID
                      WHERE u.User_ID=$customer_id")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Customer</title>
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
        input, select {
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
        input:focus, select:focus {
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
    <script>
        // تحسين JS: التحقق من الحقول قبل الإرسال
        function validateForm() {
            let email = document.querySelector('input[name="email"]').value;
            let password = document.querySelector('input[name="password_plain"]').value;
            let confirmPassword = document.querySelector('input[name="password_confirm"]').value;

            if (!email.includes('@')) {
                alert('Please enter a valid email!');
                return false;
            }
            if (password !== confirmPassword) {
                alert('Passwords do not match!');
                return false;
            }
            return true;
        }
    </script>
</head>
<body>

<h2>Edit Customer Info</h2>

<?php 
if ($message) echo "<div class='message'>$message</div>"; 
if ($error) echo "<div class='message error'>$error</div>"; 
?>

<form method="POST" onsubmit="return validateForm()">
    <label for="first_name">First Name:</label>
    <input type="text" name="first_name" value="<?= h($user['First_Name']) ?>" required>

    <label for="last_name">Last Name:</label>
    <input type="text" name="last_name" value="<?= h($user['Last_Name']) ?>" required>

    <label for="email">Email:</label>
    <input type="email" name="email" value="<?= h($user['Email']) ?>" required>

    <label for="sex">Sex:</label>
    <select name="sex" required>
        <option value="Male" <?= h($user['Sex'] == 'Male' ? 'selected' : '') ?>>Male</option>
        <option value="Female" <?= h($user['Sex'] == 'Female' ? 'selected' : '') ?>>Female</option>
        <option value="Other" <?= h($user['Sex'] == 'Other' ? 'selected' : '') ?>>Other</option>
    </select>

    <label for="points">Points:</label>
    <input type="number" name="points" value="<?= h($user['points']) ?>" min="0">

    <hr>

    <h3>Change Password (Optional)</h3>

    <label for="password_plain">New Password:</label>
    <input type="password" name="password_plain">

    <label for="password_confirm">Confirm New Password:</label>
    <input type="password" name="password_confirm">

    <button type="submit" name="update">Update Customer</button>
</form>

<br>
<form action="view_all_customers.php" method="GET" class="back-form">
    <button type="submit">Back to Customers List</button>
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
