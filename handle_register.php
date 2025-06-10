<?php
// handle_register.php

// เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูล
require_once "config/db_connect.php";

// รับค่าจากฟอร์มด้วย method POST
$username = $_POST['username'];
$full_name = $_POST['full_name'];
$password = $_POST['password'];

// --- การตรวจสอบข้อมูล (Validation) ---
// (ในโปรเจกต์จริงควรตรวจสอบให้ละเอียดกว่านี้ เช่น username ไม่ซ้ำ, รหัสผ่านแข็งแรง)

// --- การเข้ารหัสรหัสผ่าน (Password Hashing) ---
// นี่คือส่วนที่สำคัญมาก! ห้ามเก็บรหัสผ่านเป็นข้อความธรรมดาเด็ดขาด
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// --- เตรียมคำสั่ง SQL เพื่อบันทึกข้อมูล ---
// การใช้ Prepared Statements (เครื่องหมาย ?) ช่วยป้องกัน SQL Injection
$sql = "INSERT INTO users (username, full_name, password) VALUES (?, ?, ?)";

if($stmt = mysqli_prepare($conn, $sql)){
    // ผูกตัวแปรเข้ากับ statement ที่เตรียมไว้
    // "sss" หมายถึง ตัวแปรทั้ง 3 ตัวเป็นชนิด String
    mysqli_stmt_bind_param($stmt, "sss", $param_username, $param_fullname, $param_password);
    
    // กำหนดค่าให้กับพารามิเตอร์
    $param_username = $username;
    $param_fullname = $full_name;
    $param_password = $hashed_password;
    
    // พยายาม execute statement
    if(mysqli_stmt_execute($stmt)){
        // ถ้าสำเร็จ, พาไปยังหน้า login
        echo "สมัครสมาชิกสำเร็จแล้ว!";
        header("refresh:2;url=login.php"); // หน่วงเวลา 2 วินาทีก่อน redirect
        exit();
    } else{
        echo "มีบางอย่างผิดพลาด โปรดลองอีกครั้ง";
    }

    // ปิด statement
    mysqli_stmt_close($stmt);
}

// ปิดการเชื่อมต่อ
mysqli_close($conn);

?>