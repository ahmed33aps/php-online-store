<?php
// Local operator utility. Never exposed as an HTTP endpoint.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$username=$argv[1] ?? '';
$email=$argv[2] ?? '';
if (!preg_match('/^[A-Za-z0-9_.-]{3,64}$/',$username) || !filter_var($email,FILTER_VALIDATE_EMAIL)) {
 fwrite(STDERR,"Usage: php create_admin.php USERNAME EMAIL < password-file\nUse a securely created local file, then remove it. Never pass the password as an argument.\n");exit(1);
}
$password=rtrim(stream_get_contents(STDIN),"\r\n");
if(strlen($password)<12) {fwrite(STDERR,"Password must have at least 12 characters.\n");exit(1);}
$conn=new mysqli(getenv('DB_HOST')?:'127.0.0.1',getenv('DB_USER')?:'',getenv('DB_PASSWORD')?:'',getenv('DB_NAME')?:'online_store',(int)(getenv('DB_PORT')?:3306));
$conn->set_charset('utf8mb4');$hash=password_hash($password,PASSWORD_DEFAULT);unset($password);
$conn->begin_transaction();
try {
 $stmt=$conn->prepare("INSERT INTO users (User_Name,First_Name,Last_Name,Sex,Register_Date,password,Email) VALUES (?, 'Store', 'Administrator','',CURDATE(),?,?)");
 $stmt->bind_param('sss',$username,$hash,$email);$stmt->execute();$id=$conn->insert_id;
 $stmt=$conn->prepare('INSERT INTO administrators (Admin_ID) VALUES (?)');$stmt->bind_param('i',$id);$stmt->execute();$conn->commit();echo "Administrator created.\n";
} catch(Throwable $e){$conn->rollback();fwrite(STDERR,"Administrator creation failed. Check database and duplicate username.\n");exit(1);}
