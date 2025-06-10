<?php
// login.php

// เริ่ม session
session_start();
require_once "config/init_lang.php";
 
// ถ้าผู้ใช้ล็อกอินอยู่แล้ว ให้ redirect ไปยังหน้า dashboard
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="<?php echo $current_lang?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo $LANG['login_title']; ?></title>
    </head>
<body>
     <div style="text-align: right;">
        <a href="?lang=th">ไทย</a> | <a href="?lang=en">English</a>
    </div>

    <h2><?php echo $LANG['login_title']; ?></h2>
    <p>กรุณากรอกชื่อผู้ใช้และรหัสผ่านเพื่อเข้าสู่ระบบ</p>

    <form action="handle_login.php" method="post">
        <div>
            <label><?php echo $LANG['username']; ?></label>
            <input type="text" name="username" required>
        </div>    
        <div>
            <label><?php echo $LANG['password']; ?></label>
            <input type="password" name="password" required>
        </div>
        <div>
            <input type="submit" value="<?php echo $LANG['login_button']; ?>">
        </div>
        <p><?php echo $LANG['no_account']; ?> <a href="register.php"><?php echo $LANG['register_button']; ?></a>.</p>
    </form>

</body>
</html>