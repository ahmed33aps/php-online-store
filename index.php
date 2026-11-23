<?php require_once __DIR__."/security.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
    <style>
        /* CSS محدث لديزاين أنيق وشياك */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;  /* خط أنيق وحديث */
            background: linear-gradient(135deg, #1a1a2e, #16213e, #0f3460);  /* تدرج داكن أنيق (أزرق داكن مع لمسات سوداء) */
            color: #e9ecef;  /* لون نص فاتح للتباين */
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
            position: relative;
            overflow: hidden;  /* إخفاء أي تجاوز */
        }
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 25% 25%, rgba(255,255,255,.05), transparent 60%);  /* صورة خلفية شياكة (استبدل برابط صورة حقيقية إذا عندك) */
            background-size: cover;
            opacity: 0.1;  /* شفافية خفيفة عشان ما يغطي النص */
            z-index: -1;
        }
        h1 {
            color: #ffd700;  /* ذهبي للعنوان */
            font-size: 3.5em;
            margin-bottom: 20px;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.7);  /* ظل قوي للنص */
            font-weight: bold;
        }
        p {
            font-size: 1.4em;
            margin-bottom: 40px;
            opacity: 0.95;
        }
        a {
            display: inline-block;
            padding: 18px 35px;
            margin: 0 20px;
            background: linear-gradient(45deg, #ff6b6b, #feca57);  /* تدرج أحمر-برتقالي أنيق */
            color: white;
            text-decoration: none;
            border-radius: 30px;  /* حواف دائرية أكتر */
            font-weight: bold;
            font-size: 1.1em;
            transition: all 0.4s ease;  /* انتقال سلس */
            box-shadow: 0 8px 15px rgba(0,0,0,0.3);  /* ظل عميق */
            border: 2px solid transparent;
        }
        a:hover {
            background: linear-gradient(45deg, #ff5252, #ffb74d);  /* تدرج أفتح عند التمرير */
            transform: translateY(-5px) scale(1.05);  /* رفع وتكبير خفيف */
            box-shadow: 0 12px 20px rgba(0,0,0,0.4);
            border-color: #ffd700;  /* حدود ذهبية */
        }
    
/* Compact inline actions, keeping full-page forms unchanged. */
td form {display:inline-block;margin:0;padding:0;background:none;box-shadow:none;border:0;width:auto;max-width:none;}
td form button {margin:4px;}
</style>
</head>
<body>

<h1>Welcome to Online Store</h1>
<p>Do you want to login or register?</p>

<!-- زر الدخول -->
<a href="login.php" id="login-link">Login</a> | 
<!-- زر التسجيل -->
<a href="register.php" id="register-link">Register</a>

<script>
    // JavaScript مع تحسين (إضافة تأثير fade-in للصفحة)
    document.addEventListener('DOMContentLoaded', function() {
        document.body.style.opacity = '0';
        setTimeout(() => {
            document.body.style.transition = 'opacity 1s';
            document.body.style.opacity = '1';
        }, 100);
    });
    
    document.getElementById('login-link').addEventListener('click', function(event) {
        alert('Redirecting to Login page... Enjoy your shopping!');
    });
    
    document.getElementById('register-link').addEventListener('click', function(event) {
        alert('Redirecting to Register page... Welcome aboard!');
    });
</script>

</body>
</html>
