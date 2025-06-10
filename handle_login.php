<?php
// handle_login.php

// เริ่ม session เสมอเมื่อต้องการใช้งาน
session_start();

// เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูล
require_once "config/db_connect.php";

// รับค่าจากฟอร์ม
$username = $_POST['username'];
$password = $_POST['password'];

// เตรียม SQL เพื่อป้องกัน SQL Injection
$sql = "SELECT id, username, password, full_name, role FROM users WHERE username = ?";

if($stmt = mysqli_prepare($conn, $sql)){
    mysqli_stmt_bind_param($stmt, "s", $param_username);
    $param_username = $username;
    
    if(mysqli_stmt_execute($stmt)){
        mysqli_stmt_store_result($stmt);
        
        // ตรวจสอบว่ามี username นี้ในระบบหรือไม่
        if(mysqli_stmt_num_rows($stmt) == 1){                    
            // ถ้ามี, ให้ดึงข้อมูลมาเก็บในตัวแปร
            mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $full_name, $role);
            if(mysqli_stmt_fetch($stmt)){
                // ตรวจสอบรหัสผ่าน
                if(password_verify($password, $hashed_password)){
                    // รหัสผ่านถูกต้อง, เริ่ม session ใหม่
                    
                    // เก็บข้อมูลลงใน session variables
                    $_SESSION["loggedin"] = true;
                    $_SESSION["id"] = $id;
                    $_SESSION["username"] = $username;
                    $_SESSION["full_name"] = $full_name;
                    $_SESSION["role"] = $role;                            
                    
                    // ส่งผู้ใช้ไปยังหน้า dashboard
                    header("location: dashboard.php");
                } else{
                    // รหัสผ่านไม่ถูกต้อง
                    echo "รหัสผ่านไม่ถูกต้อง";
                    header("refresh:2;url=login.php");
                }
            }
        } else{
            // ไม่พบ username
            echo "ไม่พบชื่อผู้ใช้นี้ในระบบ";
            header("refresh:2;url=login.php");
        }
    } else{
        echo "มีบางอย่างผิดพลาด โปรดลองอีกครั้ง";
    }

    // ปิด statement
    mysqli_stmt_close($stmt);
}

// ปิดการเชื่อมต่อ
mysqli_close($conn);
?>