<?php
// handle_booking.php
session_start();
require_once "config/db_connect.php";

// ตรวจสอบว่าล็อกอินและกดปุ่ม submit มาหรือไม่
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SERVER["REQUEST_METHOD"] != "POST") {
    header("location: login.php");
    exit;
}

// รับข้อมูลจากฟอร์ม
$room_id = $_POST['room_id'];
$user_id = $_SESSION['id']; // ID ของผู้ใช้ที่ล็อกอินอยู่
$purpose = $_POST['purpose'];
$start_time = $_POST['start_time'];
$end_time = $_POST['end_time'];

// ตรวจสอบความถูกต้องของข้อมูลเบื้องต้น
if (empty($room_id) || empty($purpose) || empty($start_time) || empty($end_time)) {
    $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบถ้วน";
    header("location: booking.php");
    exit;
}

if ($start_time >= $end_time) {
    $_SESSION['error'] = "เวลาสิ้นสุดต้องอยู่หลังเวลาเริ่มต้น";
    header("location: booking.php");
    exit;
}


// --- ส่วนที่สำคัญที่สุด: ตรวจสอบการจองซ้อน (Conflict Check) ---
// เงื่อนไขการจองซ้อนคือ:
// (เวลาเริ่มต้นใหม่ < เวลาสิ้นสุดเดิม) AND (เวลาสิ้นสุดใหม่ > เวลาเริ่มต้นเดิม)
$sql_check = "SELECT id FROM bookings 
              WHERE room_id = ? 
              AND status = 'confirmed' 
              AND (? < end_time AND ? > start_time)";

if ($stmt_check = mysqli_prepare($conn, $sql_check)) {
    mysqli_stmt_bind_param($stmt_check, "iss", $room_id, $start_time, $end_time);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);

    // ถ้าเจอแถวข้อมูล แสดงว่ามีคนจองแล้ว
    if (mysqli_stmt_num_rows($stmt_check) > 0) {
        $_SESSION['error'] = "ขออภัย, ช่วงเวลาที่ท่านเลือกสำหรับห้องนี้ถูกจองไปแล้ว";
        header("location: booking.php");
        exit;
    }
    mysqli_stmt_close($stmt_check);
} else {
    $_SESSION['error'] = "เกิดข้อผิดพลาดในการตรวจสอบข้อมูล";
    header("location: booking.php");
    exit;
}


// --- ถ้าไม่ซ้อน ให้ทำการบันทึกข้อมูลการจอง ---
$sql_insert = "INSERT INTO bookings (user_id, room_id, purpose, start_time, end_time) VALUES (?, ?, ?, ?, ?)";

if ($stmt_insert = mysqli_prepare($conn, $sql_insert)) {
    mysqli_stmt_bind_param($stmt_insert, "iisss", $user_id, $room_id, $purpose, $start_time, $end_time);

    if (mysqli_stmt_execute($stmt_insert)) {
        $_SESSION['message'] = "ทำการจองห้องประชุมสำเร็จแล้ว!";
    } else {
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูลการจอง";
    }
    mysqli_stmt_close($stmt_insert);
}

mysqli_close($conn);
header("location: booking.php");
exit;
?>