<?php
// admin_manage_rooms.php

session_start();
require_once "config/db_connect.php";

// =================== การตรวจสอบสิทธิ์ ===================
// ตรวจสอบว่าผู้ใช้ล็อกอินอยู่หรือไม่ และเป็น admin หรือไม่
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("location: login.php"); // ถ้าไม่ใช่ ให้ส่งกลับไปหน้า login
    exit;
}
// ======================================================


// =================== ตรรกะ CRUD =======================
$message = '';
$error = '';
$edit_room = null; // ตัวแปรสำหรับเก็บข้อมูลห้องที่จะแก้ไข

// --- CREATE: การเพิ่มห้องใหม่ ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_room'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $capacity = $_POST['capacity'];

    $sql = "INSERT INTO rooms (name, description, capacity) VALUES (?, ?, ?)";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ssi", $name, $description, $capacity);
        if (mysqli_stmt_execute($stmt)) {
            $message = "เพิ่มห้องประชุมสำเร็จแล้ว!";
        } else {
            $error = "เกิดข้อผิดพลาดในการเพิ่มข้อมูล: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

// --- UPDATE: การแก้ไขข้อมูลห้อง ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_room'])) {
    $id = $_POST['room_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $capacity = $_POST['capacity'];

    $sql = "UPDATE rooms SET name = ?, description = ?, capacity = ? WHERE id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ssii", $name, $description, $capacity, $id);
        if (mysqli_stmt_execute($stmt)) {
            $message = "อัปเดตข้อมูลห้องสำเร็จแล้ว!";
        } else {
            $error = "เกิดข้อผิดพลาดในการอัปเดตข้อมูล: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

// --- DELETE: การลบห้อง ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    // ในระบบจริง ควรตรวจสอบก่อนว่ามี booking ที่ผูกกับห้องนี้หรือไม่
    $sql = "DELETE FROM rooms WHERE id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            header("location: admin_manage_rooms.php"); // Redirect เพื่อล้าง query string
            exit();
        } else {
            $error = "เกิดข้อผิดพลาดในการลบข้อมูล: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

// --- ตรรกะสำหรับการแก้ไข: ดึงข้อมูลห้องมาแสดงในฟอร์ม ---
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $sql = "SELECT * FROM rooms WHERE id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            $edit_room = mysqli_fetch_assoc($result);
        }
        mysqli_stmt_close($stmt);
    }
}


// --- READ: ดึงข้อมูลห้องทั้งหมดมาแสดง ---
$sql = "SELECT * FROM rooms ORDER BY created_at DESC";
$rooms_result = mysqli_query($conn, $sql);
$rooms = mysqli_fetch_all($rooms_result, MYSQLI_ASSOC);

// ======================================================

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการห้องประชุม</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .message { color: green; }
        .error { color: red; }
        .form-card { border: 1px solid #ccc; padding: 20px; margin-bottom: 20px; border-radius: 5px; }
    </style>
</head>
<body>

    <h1>จัดการห้องประชุม</h1>
    <p>ยินดีต้อนรับ, <?php echo htmlspecialchars($_SESSION["full_name"]); ?> (Admin)</p>
    <a href="dashboard.php">กลับไปที่ Dashboard</a>
    <hr>

    <?php if ($message): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <div class="form-card">
        <h3><?php echo $edit_room ? 'แก้ไขข้อมูลห้องประชุม' : 'เพิ่มห้องประชุมใหม่'; ?></h3>
        <form action="admin_manage_rooms.php" method="post">
            
            <?php if ($edit_room): ?>
                <input type="hidden" name="room_id" value="<?php echo $edit_room['id']; ?>">
            <?php endif; ?>

            <div>
                <label>ชื่อห้อง:</label>
                <input type="text" name="name" value="<?php echo $edit_room['name'] ?? ''; ?>" required>
            </div>
            <br>
            <div>
                <label>คำอธิบาย:</label>
                <textarea name="description"><?php echo $edit_room['description'] ?? ''; ?></textarea>
            </div>
            <br>
            <div>
                <label>ความจุ (คน):</label>
                <input type="number" name="capacity" value="<?php echo $edit_room['capacity'] ?? ''; ?>" required>
            </div>
            <br>
            <div>
                <?php if ($edit_room): ?>
                    <button type="submit" name="update_room">อัปเดตข้อมูล</button>
                    <a href="admin_manage_rooms.php">ยกเลิกการแก้ไข</a>
                <?php else: ?>
                    <button type="submit" name="add_room">เพิ่มห้องประชุม</button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <hr>

    <h3>รายการห้องประชุมทั้งหมด</h3>
    <table>
        <thead>
            <tr>
                <th>ชื่อห้อง</th>
                <th>คำอธิบาย</th>
                <th>ความจุ</th>
                <th>การจัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rooms as $room): ?>
                <tr>
                    <td><?php echo htmlspecialchars($room['name']); ?></td>
                    <td><?php echo htmlspecialchars($room['description']); ?></td>
                    <td><?php echo htmlspecialchars($room['capacity']); ?></td>
                    <td>
                        <a href="admin_manage_rooms.php?edit=<?php echo $room['id']; ?>">แก้ไข</a> |
                        <a href="admin_manage_rooms.php?delete=<?php echo $room['id']; ?>" onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบห้องนี้?');">ลบ</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($rooms)): ?>
                <tr>
                    <td colspan="4">ยังไม่มีห้องประชุมในระบบ</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>