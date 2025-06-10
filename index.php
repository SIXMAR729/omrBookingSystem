<?php
// index.php

session_start();
require_once 'config/init_lang.php';

// กำหนด title สำหรับหน้านี้โดยเฉพาะ
$page_title = 'หน้าแรก - ระบบจองห้องประชุม';

// เรียกใช้ header
require_once 'templates/header.php';
?>

<div class="container">
    <h1><?php echo $LANG['welcome']; ?>!</h1>
    <p>นี่คือระบบจองห้องประชุมออนไลน์ที่สร้างด้วย PHP โปรดเข้าสู่ระบบเพื่อเริ่มทำการจอง</p>
    <p>This is an online meeting room booking system built with PHP. Please login to start booking.</p>
    
    <?php if (!isset($_SESSION['loggedin'])): ?>
        <a href="login.php" class="button-primary"><?php echo $LANG['login_button']; ?></a>
    <?php endif; ?>
</div>

<?php
// เรียกใช้ footer
require_once 'templates/footer.php';
?>