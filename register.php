<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>สมัครสมาชิก</title>
    </head>
<body>

    <h2>สมัครสมาชิก</h2>
    <p>กรุณากรอกข้อมูลเพื่อสร้างบัญชีใหม่</p>

    <form action="handle_register.php" method="post">
        <div>
            <label>ชื่อผู้ใช้ (Username)</label>
            <input type="text" name="username" required>
        </div>
        <div>
            <label>ชื่อ-นามสกุล</label>
            <input type="text" name="full_name" required>
        </div>
        <div>
            <label>รหัสผ่าน</label>
            <input type="password" name="password" required>
        </div>
        <div>
            <input type="submit" value="สมัครสมาชิก">
        </div>
        <p>มีบัญชีอยู่แล้ว? <a href="login.php">เข้าสู่ระบบที่นี่</a>.</p>
    </form>

</body>
</html>