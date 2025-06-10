<?php
// dashboard.php

// เริ่ม session และตรวจสอบการล็อกอิน
session_start();
 
// ถ้าไม่ได้ล็อกอิน, ให้ส่งกลับไปหน้า login
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
?>
 
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
</head>
<body>
    <h1>สวัสดี, <?php echo htmlspecialchars($_SESSION["full_name"]); ?>!</h1>
    <p>ยินดีต้อนรับสู่ระบบจองห้องประชุม</p>
    <hr>

    <h3>เมนูของคุณ</h3>
    
    <?php
    // ตรวจสอบบทบาท (role) ของผู้ใช้
    if ($_SESSION['role'] == 'admin') {
        // --- เมนูสำหรับ Admin ---
        echo "<h4>ส่วนของผู้ดูแลระบบ:</h4>";
        echo "<ul>";
        echo "<li><a href='admin_manage_rooms.php'>จัดการห้องประชุม</a></li>";
        echo "<li><a href='admin_manage_bookings.php'>ดูการจองทั้งหมด</a></li>";
        echo "<li><a href='admin_manage_users.php'>จัดการผู้ใช้</a></li>";
        echo "</ul>";
    }
    ?>

    <h4>ส่วนของผู้ใช้ทั่วไป:</h4>
    <ul>
        <li><a href="booking.php">จองห้องประชุม</a></li>
        <li><a href="my_bookings.php">ดูการจองของฉัน</a></li>
    </ul>

    <hr>
    <p>
        <a href="logout.php">ออกจากระบบ</a>
    </p>
</body>
</html>