<?php
// admin_manage_users.php
session_start();
require_once "config/db_connect.php";

// =================== การตรวจสอบสิทธิ์ ===================
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("location: login.php");
    exit;
}
// ======================================================

$message = '';
$error = '';
$edit_user = null;

// --- CREATE: เพิ่มผู้ใช้ใหม่ ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $full_name = $_POST['full_name'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // เข้ารหัสผ่านก่อนบันทึกเสมอ
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, full_name, password, role) VALUES (?, ?, ?, ?)";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ssss", $username, $full_name, $hashed_password, $role);
        if (mysqli_stmt_execute($stmt)) {
            $message = "เพิ่มผู้ใช้ใหม่สำเร็จ!";
        } else {
            // ตรวจสอบ lỗi username ซ้ำ
            if (mysqli_errno($conn) == 1062) {
                $error = "Username นี้มีผู้ใช้งานแล้ว โปรดเลือกชื่ออื่น";
            } else {
                $error = "เกิดข้อผิดพลาดในการเพิ่มข้อมูล: " . mysqli_error($conn);
            }
        }
        mysqli_stmt_close($stmt);
    }
}

// --- UPDATE: แก้ไขข้อมูลผู้ใช้ ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user'])) {
    $id = $_POST['user_id'];
    $full_name = $_POST['full_name'];
    $role = $_POST['role'];

    $sql = "UPDATE users SET full_name = ?, role = ? WHERE id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ssi", $full_name, $role, $id);
        if (mysqli_stmt_execute($stmt)) {
            $message = "อัปเดตข้อมูลผู้ใช้สำเร็จ!";
        } else {
            $error = "เกิดข้อผิดพลาดในการอัปเดตข้อมูล: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

// --- DELETE: ลบผู้ใช้ ---
if (isset($_GET['delete'])) {
    $id_to_delete = $_GET['delete'];

    // !!! จุดสำคัญด้านความปลอดภัย: ห้ามแอดมินลบตัวเอง !!!
    if ($id_to_delete == $_SESSION['id']) {
        $error = "คุณไม่สามารถลบบัญชีของตัวเองได้!";
    } else {
        // ในระบบจริง ควรพิจารณาว่าจะทำอย่างไรกับการจอง (bookings) ของผู้ใช้ที่ถูกลบ
        $sql = "DELETE FROM users WHERE id = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $id_to_delete);
            if (mysqli_stmt_execute($stmt)) {
                header("location: admin_manage_users.php");
                exit();
            } else {
                $error = "เกิดข้อผิดพลาดในการลบข้อมูล: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// --- ตรรกะสำหรับการแก้ไข: ดึงข้อมูลผู้ใช้มาแสดงในฟอร์ม ---
if (isset($_GET['edit'])) {
    $id_to_edit = $_GET['edit'];
    $sql = "SELECT id, username, full_name, role FROM users WHERE id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id_to_edit);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            $edit_user = mysqli_fetch_assoc($result);
        }
        mysqli_stmt_close($stmt);
    }
}

// --- READ: ดึงข้อมูลผู้ใช้ทั้งหมดมาแสดง ---
$sql_users = "SELECT id, username, full_name, role, created_at FROM users ORDER BY created_at DESC";
$users_result = mysqli_query($conn, $sql_users);
$users = mysqli_fetch_all($users_result, MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการผู้ใช้</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .form-card { border: 1px solid #ccc; padding: 20px; margin-bottom: 20px; border-radius: 5px; }
        .message { color: green; }
        .error { color: red; }
    </style>
</head>
<body>

    <h1>จัดการผู้ใช้</h1>
    <a href="dashboard.php">กลับไปที่ Dashboard</a>
    <hr>

    <?php if ($message): ?><p class="message"><?php echo $message; ?></p><?php endif; ?>
    <?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>

    <div class="form-card">
        <h3><?php echo $edit_user ? 'แก้ไขข้อมูลผู้ใช้' : 'เพิ่มผู้ใช้ใหม่'; ?></h3>
        <form action="admin_manage_users.php" method="post">
            
            <?php if ($edit_user): ?>
                <input type="hidden" name="user_id" value="<?php echo $edit_user['id']; ?>">
            <?php endif; ?>

            <div>
                <label>Username:</label>
                <input type="text" name="username" value="<?php echo $edit_user['username'] ?? ''; ?>" <?php echo $edit_user ? 'readonly' : 'required'; ?>>
            </div>
            <br>
            <div>
                <label>ชื่อ-นามสกุล:</label>
                <input type="text" name="full_name" value="<?php echo $edit_user['full_name'] ?? ''; ?>" required>
            </div>
            <br>
            
            <?php if (!$edit_user): // แสดงช่องรหัสผ่านเฉพาะตอน "เพิ่มผู้ใช้ใหม่" ?>
            <div>
                <label>รหัสผ่าน:</label>
                <input type="password" name="password" required>
            </div>
            <br>
            <?php endif; ?>

            <div>
                <label>บทบาท (Role):</label>
                <select name="role" required>
                    <option value="user" <?php echo ($edit_user && $edit_user['role'] == 'user') ? 'selected' : ''; ?>>User</option>
                    <option value="admin" <?php echo ($edit_user && $edit_user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
            <br>
            <div>
                <?php if ($edit_user): ?>
                    <button type="submit" name="update_user">อัปเดตข้อมูล</button>
                    <a href="admin_manage_users.php">ยกเลิกการแก้ไข</a>
                <?php else: ?>
                    <button type="submit" name="add_user">เพิ่มผู้ใช้</button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <hr>

    <h3>รายการผู้ใช้ในระบบ</h3>
    <table>
        <thead>
            <tr>
                <th>Username</th>
                <th>ชื่อ-นามสกุล</th>
                <th>บทบาท</th>
                <th>วันที่สมัคร</th>
                <th>การจัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                    <td>
                        <a href="admin_manage_users.php?edit=<?php echo $user['id']; ?>">แก้ไข</a> 
                        <?php if ($user['id'] != $_SESSION['id']): // ซ่อนปุ่มลบถ้าเป็น user ตัวเอง ?>
                            | <a href="admin_manage_users.php?delete=<?php echo $user['id']; ?>" onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบผู้ใช้นี้? การกระทำนี้ไม่สามารถย้อนกลับได้');">ลบ</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>
</html>