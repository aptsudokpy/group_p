<?php
// config/db.php
$host = "localhost";
$user = "root";
$pass = "123456789";
$dbname = "group_p";

try {
    // ใช้ PDO เพื่อความปลอดภัยและทันสมัย (ป้องกัน SQL Injection ได้ดีกว่า mysqli)
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    // ตั้งค่าให้แสดง Error หากเชื่อมต่อไม่ได้
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

// เริ่ม Session ทุกครั้งที่มีการเรียกใช้ไฟล์นี้
// ตรวจสอบสถานะ Session ก่อนเปิด (ถ้ายังไม่มี Session ให้ start)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>