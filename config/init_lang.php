<?php
// config/init_lang.php

// session_start() ต้องถูกเรียกใช้ก่อนในหน้าที่เรียกไฟล์นี้
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. กำหนดภาษาเริ่มต้น
$default_lang = 'th';

// 2. ตรวจสอบว่ามีการส่งค่า lang มาทาง URL หรือไม่ (เช่น ?lang=en)
if (isset($_GET['lang'])) {
    $lang_choice = $_GET['lang'];
    // ตรวจสอบว่าเป็นภาษาที่เรารองรับหรือไม่
    if ($lang_choice == 'en' || $lang_choice == 'th') {
        $_SESSION['lang'] = $lang_choice;
    }
}

// 3. กำหนดภาษาที่จะใช้งาน
// ถ้ามีใน session ให้ใช้ค่าใน session, ถ้าไม่มีให้ใช้ค่าเริ่มต้น
$current_lang = $_SESSION['lang'] ?? $default_lang;

// 4. โหลดไฟล์ภาษาที่ถูกต้อง
$lang_file = __DIR__ . '/../lang/' . $current_lang . '.php';

if (file_exists($lang_file)) {
    $LANG = require_once($lang_file);
} else {
    // กรณีหาไฟล์ไม่เจอ ให้ใช้ภาษาเริ่มต้นเสมอ
    $LANG = require_once(__DIR__ . '/../lang/' . $default_lang . '.php');
}

?>