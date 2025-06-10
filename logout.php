<?php
// logout.php

// เริ่ม session
session_start();
 
// ทำลาย session variables ทั้งหมด
$_SESSION = array();
 
// ทำลาย session
session_destroy();
 
// Redirect ไปยังหน้า login
header("location: login.php");
exit;
?>