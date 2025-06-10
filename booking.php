<?php

// booking.php
session_start();
require_once "config/db_connect.php";

// ตรวจสอบว่าล็อกอินหรือยัง
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// ดึงข้อมูลห้องทั้งหมดเพื่อมาสร้าง dropdown
$sql_rooms = "SELECT * FROM rooms ORDER BY name ASC";
$rooms_result = mysqli_query($conn, $sql_rooms);
$rooms = mysqli_fetch_all($rooms_result, MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จองห้องประชุม</title>
    <style>
        .form-card { border: 1px solid #ccc; padding: 20px; margin-bottom: 20px; border-radius: 5px; max-width: 500px; }
        .message { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>จองห้องประชุม</h1>
    <a href="dashboard.php">กลับไปที่ Dashboard</a> | <a href="my_bookings.php">ดูการจองของฉัน</a>
    <hr>

    <div class="form-card">
        <h3>กรอกรายละเอียดการจอง</h3>

        <?php if(isset($_SESSION['message'])): ?>
            <p class="message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></p>
        <?php endif; ?>
        <?php if(isset($_SESSION['error'])): ?>
            <p class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
        <?php endif; ?>

        <form action="handle_booking.php" method="post">
            <div>
                <label for="room_id">เลือกห้องประชุม:</label><br>
                <select name="room_id" id="room_id" required>
                    <option value="">-- กรุณาเลือกห้อง --</option>
                    <?php foreach ($rooms as $room): ?>
                        <option value="<?php echo $room['id']; ?>"><?php echo htmlspecialchars($room['name']) . " (ความจุ " . $room['capacity'] . " คน)"; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <br>
            <div>
                <label for="purpose">วัตถุประสงค์การจอง:</label><br>
                <input type="text" name="purpose" id="purpose" required style="width: 95%;">
            </div>
            <br>
            <div>
                <label for="start_time">เวลาเริ่ม:</label><br>
                <input type="datetime-local" id="start_time" name="start_time" required>
            </div>
            <br>
            <div>
                <label for="end_time">เวลาสิ้นสุด:</label><br>
                <input type="datetime-local" id="end_time" name="end_time" required>
            </div>
            <br>
            <button type="submit" name="submit_booking">ยืนยันการจอง</button>
        </form>
    </div>

</body>
</html>