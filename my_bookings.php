<?php
// my_bookings.php
session_start();
require_once "config/db_connect.php";

// ตรวจสอบว่าล็อกอินหรือยัง
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// ดึง user_id จาก session เพื่อใช้ใน query
$user_id = $_SESSION['id'];
$message = '';
$error = '';

// --- ตรรกะการยกเลิกการจอง ---
// เพิ่มการตรวจสอบ user_id เพื่อความปลอดภัยสูงสุด
if (isset($_GET['cancel'])) {
    $booking_id_to_cancel = $_GET['cancel'];

    // SQL จะอัปเดตก็ต่อเมื่อ booking id และ user id ตรงกับของที่ล็อกอินอยู่เท่านั้น
    $sql_cancel = "UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql_cancel)) {
        mysqli_stmt_bind_param($stmt, "ii", $booking_id_to_cancel, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                $message = "ยกเลิกการจองสำเร็จแล้ว";
            } else {
                $error = "ไม่สามารถยกเลิกการจองนี้ได้ อาจไม่ใช่การจองของคุณ";
            }
        } else {
            $error = "เกิดข้อผิดพลาดในการยกเลิก";
        }
        mysqli_stmt_close($stmt);
    }
}

// --- ดึงข้อมูลการจอง "เฉพาะ" ของผู้ใช้ที่ล็อกอินอยู่ ---
$sql = "SELECT 
            b.id, 
            b.start_time, 
            b.end_time, 
            b.purpose, 
            b.status, 
            r.name AS room_name
        FROM bookings AS b
        JOIN rooms AS r ON b.room_id = r.id
        WHERE b.user_id = ?
        ORDER BY b.start_time DESC";

$bookings = []; // กำหนดค่าเริ่มต้นเป็น array ว่าง
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $bookings = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
}

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>การจองของฉัน</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .status-confirmed { color: green; font-weight: bold; }
        .status-cancelled { color: red; text-decoration: line-through; }
    </style>
</head>
<body>
    <h1>ประวัติการจองของฉัน</h1>
    <a href="dashboard.php">กลับไปที่ Dashboard</a> | <a href="booking.php">จองห้องเพิ่ม</a>
    <hr>
    
    <?php if ($message): ?><p style="color:green;"><?php echo $message; ?></p><?php endif; ?>
    <?php if ($error): ?><p style="color:red;"><?php echo $error; ?></p><?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ห้องประชุม</th>
                <th>วัตถุประสงค์</th>
                <th>เวลาเริ่ม</th>
                <th>เวลาสิ้นสุด</th>
                <th>สถานะ</th>
                <th>การจัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($bookings)): ?>
                <tr><td colspan="6">คุณยังไม่มีประวัติการจอง</td></tr>
            <?php else: ?>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($booking['room_name']); ?></td>
                        <td><?php echo htmlspecialchars($booking['purpose']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($booking['start_time'])); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($booking['end_time'])); ?></td>
                        <td>
                            <span class="status-<?php echo $booking['status']; ?>">
                                <?php echo $booking['status'] == 'confirmed' ? 'ยืนยันแล้ว' : 'ยกเลิก'; ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                            // แสดงปุ่มยกเลิกเฉพาะการจองที่ยัง "ยืนยันแล้ว" และ "ยังมาไม่ถึง"
                            if ($booking['status'] == 'confirmed' && strtotime($booking['start_time']) > time()): 
                            ?>
                                <a href="my_bookings.php?cancel=<?php echo $booking['id']; ?>" onclick="return confirm('คุณแน่ใจหรือไม่ที่จะยกเลิกการจองนี้?');">ยกเลิก</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>