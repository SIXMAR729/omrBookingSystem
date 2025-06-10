<?php
// templates/header.php

// session_start() และ init_lang.php ควรจะถูกเรียกใช้ก่อนในไฟล์หลัก
// ที่จะเรียกใช้ header นี้

// กำหนด Title เริ่มต้น หากหน้านั้นๆ ไม่ได้ตั้งค่ามา
$page_title = $page_title ?? 'ระบบจองห้องประชุม';

?>
<!DOCTYPE html>
<html lang="<?php echo $current_lang ?? 'th'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>

    <header class="site-header">
        <nav class="navbar">
            <div class="nav-brand">
                <a href="index.php">Booking System</a>
            </div>
            <ul class="nav-menu">
                <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                    <li><a href="dashboard.php"><?php echo $LANG['dashboard']; ?></a></li>
                    <li><a href="my_bookings.php"><?php echo $LANG['my_bookings']; ?></a></li>
                    <li><a href="logout.php"><?php echo $LANG['logout']; ?></a></li>
                <?php else: ?>
                    <li><a href="login.php"><?php echo $LANG['login_button']; ?></a></li>
                    <li><a href="register.php"><?php echo $LANG['register_button']; ?></a></li>
                <?php endif; ?>
            </ul>
            <div class="lang-switcher">
                <a href="?lang=th" class="<?php echo ($current_lang == 'th') ? 'active' : ''; ?>">TH</a> | 
                <a href="?lang=en" class="<?php echo ($current_lang == 'en') ? 'active' : ''; ?>">EN</a>
            </div>
        </nav>
    </header>

    <main class="main-section">