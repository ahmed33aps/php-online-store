<?php
require_once __DIR__ . "/security.php";
require_once __DIR__ . "/db_connect.php";
if ($_SERVER['REQUEST_METHOD']==='POST') {
 if (($_POST['user_type']??'customer')!=='customer') fail_request('Only customer self-registration is allowed.',403);
 $username=trim((string)($_POST['username']??''));$email=trim((string)($_POST['email']??''));$password=(string)($_POST['password']??'');
 if (!preg_match('/^[A-Za-z0-9_.-]{3,64}$/',$username) || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($password)<12) fail_request('Use a valid username/email and a password of at least 12 characters.');
 if (one('SELECT User_ID FROM users WHERE User_Name=?',[$username])) fail_request('Username unavailable.',409);
 $conn->begin_transaction();
 try {
  sql('INSERT INTO users (User_Name,First_Name,Last_Name,Sex,Register_Date,password,Email) VALUES (?,?,?,?,CURDATE(),?,?)',[$username,trim((string)($_POST['first_name']??'')),trim((string)($_POST['last_name']??'')),(string)($_POST['sex']??''),password_hash($password,PASSWORD_DEFAULT),$email]);
  $id=$conn->insert_id; sql('INSERT INTO customers (Customer_ID,points) VALUES (?,0)',[$id]);$conn->commit();header('Location: login.php');exit;
 } catch (Throwable $e) {$conn->rollback();fail_request('Registration failed.',400);}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <style>
        /* CSS مع إصلاح السكرول والنصوص */
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
            margin-bottom: 20px;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.7);
            text-align: center;  /* تأكيد المركز */
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
        #customer_fields, #admin_fields {
            margin-top: 20px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        p {
            text-align: center;  /* مركز الفقرات السفلية */
            margin-top: 20px;
        }
        p a {
            color: #ffd700;
            text-decoration: none;
        }
        p a:hover {
            text-decoration: underline;
        }
    
/* Compact inline actions, keeping full-page forms unchanged. */
td form {display:inline-block;margin:0;padding:0;background:none;box-shadow:none;border:0;width:auto;max-width:none;}
td form button {margin:4px;}
</style>
    <script>
        function changeForm() {
            let type = document.getElementById("user_type").value;

            document.getElementById("customer_fields").style.display = "none";
            document.getElementById("admin_fields").style.display = "none";

            if (type === "customer") {
                document.getElementById("customer_fields").style.display = "block";
            }
            if (type === "admin") {
                document.getElementById("admin_fields").style.display = "block";
            }
        }

        // تحسين JS: التحقق من الحقول قبل الإرسال
        function validateForm() {
            let username = document.querySelector('input[name="username"]').value.trim();
            let password = document.querySelector('input[name="password"]').value;
            let email = document.querySelector('input[name="email"]').value;

            if (username.length < 3) {
                alert('Username must be at least 3 characters!');
                return false;
            }
            if (password.length < 6) {
                alert('Password must be at least 6 characters!');
                return false;
            }
            if (!email.includes('@')) {
                alert('Please enter a valid email!');
                return false;
            }
            return true;
        }
    </script>
</head>
<body>

<h2>Create Account</h2>

<form action="" method="POST" autocomplete="off" onsubmit="return validateForm()">

    <label for="username">Username:</label>
    <input type="text" name="username" required><br>

    <br>

    <label for="first_name">First Name:</label>
    <input type="text" name="first_name" required><br>

    <label for="last_name">Last Name:</label>
    <input type="text" name="last_name" required><br>

    <label for="sex">Sex:</label>
    <select name="sex" required>
        <option value="">-- Select Sex --</option>
        <option value="Male">Male</option>
        <option value="Female">Female</option>
        <option value="Other">Other</option>
    </select><br>

    <label for="email">Email:</label>
    <input type="email" name="email" required><br>

    <label for="password">Password:</label>
    <input type="password" name="password" required><br>

    <label for="user_type">User Type:</label>
    <select id="user_type" name="user_type" onchange="changeForm()" required>
        <option value="">-- Select Type --</option>
        <option value="customer">Customer</option>
        
    </select><br>

    <!-- حقول Customer -->
    <div id="customer_fields" style="display:none;">
        <h4>Customer Info</h4>
        <p>Points will start at 0 automatically.</p>
    </div>

    <!-- حقول Admin -->
    

    <button type="submit">Register</button>

</form>

<p>Have an account? <a href="login.php">Login here</a></p>
<p>Go to "Welcome Page" <a href="index.php">Welcome Page</a></p>

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
