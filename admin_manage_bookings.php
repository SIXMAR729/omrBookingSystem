<?php
// admin_manage_bookings.php
session_start();
require_once "config/db_connect.php";

// การตรวจสอบสิทธิ์ (ต้องเป็น Admin เท่านั้น)
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("location: login.php");
    exit;
}

$message = '';
$error = '';

// --- ตรรกะการยกเลิกการจอง (Soft Delete) ---
if (isset($_GET['cancel'])) {
    $booking_id = $_GET['cancel'];
    $sql_cancel = "UPDATE bookings SET status = 'cancelled' WHERE id = ?";
    if ($stmt = mysqli_prepare($conn, $sql_cancel)) {
        mysqli_stmt_bind_param($stmt, "i", $booking_id);
        if (mysqli_stmt_execute($stmt)) {
            $message = "ยกเลิกการจองสำเร็จแล้ว";
        } else {
            $error = "เกิดข้อผิดพลาดในการยกเลิก";
        }
        mysqli_stmt_close($stmt);
    }
}


// --- ดึงข้อมูลการจองทั้งหมดโดย JOIN ตาราง ---
// เราจะ JOIN ตาราง bookings, users, และ rooms เพื่อดึงชื่อมาแสดงผล
$sql = "SELECT 
            b.id, 
            b.start_time, 
            b.end_time, 
            b.purpose, 
            b.status, 
            u.full_name AS user_name, 
            r.name AS room_name
        FROM bookings AS b
        JOIN users AS u ON b.user_id = u.id
        JOIN rooms AS r ON b.room_id = r.id
        ORDER BY b.start_time DESC";

$result = mysqli_query($conn, $sql);
$bookings = mysqli_fetch_all($result, MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการการจองทั้งหมด</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .status-confirmed { color: green; font-weight: bold; }
        .status-cancelled { color: red; text-decoration: line-through; }
    </style>
</head>
<body>
    <h1>จัดการการจองทั้งหมด</h1>
    <a href="dashboard.php">กลับไปที่ Dashboard</a>
    <hr>
    
    <?php if ($message): ?><p style="color:green;"><?php echo $message; ?></p><?php endif; ?>
    <?php if ($error): ?><p style="color:red;"><?php echo $error; ?></p><?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ห้องประชุม</th>
                <th>ผู้จอง</th>
                <th>วัตถุประสงค์</th>
                <th>เวลาเริ่ม</th>
                <th>เวลาสิ้นสุด</th>
                <th>สถานะ</th>
                <th>การจัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($bookings)): ?>
                <tr><td colspan="7">ไม่มีข้อมูลการจอง</td></tr>
            <?php else: ?>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($booking['room_name']); ?></td>
                        <td><?php echo htmlspecialchars($booking['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($booking['purpose']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($booking['start_time'])); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($booking['end_time'])); ?></td>
                        <td>
                            <span class="status-<?php echo $booking['status']; ?>">
                                <?php echo $booking['status'] == 'confirmed' ? 'ยืนยันแล้ว' : 'ยกเลิก'; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($booking['status'] == 'confirmed'): ?>
                                <a href="admin_manage_bookings.php?cancel=<?php echo $booking['id']; ?>" onclick="return confirm('คุณแน่ใจหรือไม่ที่จะยกเลิกการจองนี้?');">ยกเลิก</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>