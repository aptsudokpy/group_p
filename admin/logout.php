<?php
session_start();
session_unset();
session_destroy(); // ล้างข้อมูล Session ทั้งหมด

// สั่งให้เด้งไปหน้า Login ของ User (ระบุ path ให้ถูกต้อง)
header("Location: user_interface/login.php");
exit();
?>