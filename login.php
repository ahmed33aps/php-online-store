<?php
require_once __DIR__ . "/security.php";
require_once __DIR__ . "/db_connect.php";
if ($_SERVER['REQUEST_METHOD']==='POST') {
 $user=one('SELECT * FROM users WHERE User_Name=? LIMIT 1',[(string)($_POST['username']??'')]);
 if (!$user || !password_verify((string)($_POST['password']??''),$user['password'])) fail_request('Invalid username or password.',401);
 $id=(int)$user['User_ID']; $type='';
 foreach (['customer'=>['customers','Customer_ID'],'agent'=>['agents','Agent_ID'],'admin'=>['administrators','Admin_ID']] as $role=>$table) if(one("SELECT {$table[1]} FROM {$table[0]} WHERE {$table[1]}=?",[$id])) $type=$role;
 if (!$type) fail_request('Account role unavailable.',403);
 session_regenerate_id(true); $_SESSION['user_id']=$id; $_SESSION['user_type']=$type; $_SESSION['username']=$user['User_Name']; $_SESSION['csrf']=bin2hex(random_bytes(32));
 header('Location: '.$type.'_home.php'); exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
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
            max-width: 400px;  /* أصغر شوية من صفحة التسجيل عشان أبسط */
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
        input {
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
        input:focus {
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
        // تحسين JS: التحقق من الحقول قبل الإرسال
        function validateForm() {
            let username = document.querySelector('input[name="username"]').value.trim();
            let password = document.querySelector('input[name="password"]').value;

            if (username.length < 1) {
                alert('Please enter a username!');
                return false;
            }
            if (password.length < 1) {
                alert('Please enter a password!');
                return false;
            }
            return true;
        }
    </script>
</head>
<body>

<h2>Login</h2>

<form method="POST" action="" autocomplete="off" onsubmit="return validateForm()">
    <label for="username">Username:</label>
    <input type="text" name="username" required><br>

    <label for="password">Password:</label>
    <input type="password" name="password" required><br>

    <button type="submit">Login</button>
</form>

<p>Don't have an account? <a href="register.php">Register here</a></p>
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
