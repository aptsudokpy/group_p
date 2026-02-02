<?php
// 1. เริ่ม Session และเปิด Error ทันที
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ==========================================
// 2. ส่วนเชื่อมต่อฐานข้อมูล (Direct Connection)
// (แก้ 3 บรรทัดนี้ให้ตรงกับ Cloud ของคุณ)
// ==========================================
$servername = "localhost";
$username   = "เปลี่ยนเป็นUser_DirectAdmin"; // เช่น groupp_admin
$password   = "เปลี่ยนเป็นรหัสผ่านDB";
$dbname     = "เปลี่ยนเป็นชื่อDB_DirectAdmin";   // เช่น groupp_shop

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("<h3 style='color:red'>❌ เชื่อมต่อฐานข้อมูลไม่ได้: " . $e->getMessage() . "</h3>");
}
// ==========================================


// 3. ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("<div class='alert alert-danger m-5'>⛔ คุณไม่มีสิทธิ์เข้าถึง (Role ปัจจุบัน: " . ($_SESSION['role'] ?? 'ไม่มี') . ")<br><a href='../login.php'>คลิกเพื่อไปหน้า Login</a></div>");
}

// --- Logic 1: บันทึกรายงาน ---
if (isset($_POST['save_report'])) {
    $uid = $_POST['user_id'];
    $msg = $_POST['admin_message'];
    try {
        $stmt = $conn->prepare("UPDATE users SET admin_message = ? WHERE id = ?");
        $stmt->execute([$msg, $uid]);
        echo "<script>alert('✅ บันทึกเรียบร้อย'); window.location='manage_users.php';</script>";
    } catch(PDOException $e) {
        echo "Error Update: " . $e->getMessage();
    }
}

// --- Logic 2: ลบลูกค้า ---
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    try {
        $check = $conn->prepare("SELECT role FROM users WHERE id = ?");
        $check->execute([$id]);
        if ($check->fetchColumn() !== 'admin') {
            $conn->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
            echo "<script>alert('ลบเรียบร้อย'); window.location='manage_users.php';</script>";
        }
    } catch(PDOException $e) {
        echo "Error Delete: " . $e->getMessage();
    }
}

// 4. ดึงข้อมูล (ใช้ try-catch กันเหนียว)
$users = [];
try {
    // เช็คชื่อตาราง users (ตัวเล็ก)
    $stmt = $conn->prepare("SELECT * FROM users WHERE role = 'user' ORDER BY id DESC");
    $stmt->execute();
    $users = $stmt->fetchAll();
} catch(PDOException $e) {
    echo "<div class='alert alert-danger'>❌ Error SQL: " . $e->getMessage() . "<br>ลองเช็คชื่อตารางใน DB ว่าเป็น users หรือ Users</div>";
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Manage Users (Debug Mode)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <h2>🛠️ Debug Mode: Manage Users</h2>
        <a href="index.php" class="btn btn-secondary mb-3">กลับหน้า Dashboard</a>
        
        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ชื่อ</th>
                            <th>Username</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($users)): ?>
                            <tr><td colspan="4" class="text-center text-danger">ไม่พบข้อมูล หรือ SQL ผิดพลาด</td></tr>
                        <?php else: ?>
                            <?php foreach($users as $u): ?>
                            <tr>
                                <td><?php echo $u['id']; ?></td>
                                <td><?php echo $u['fullname']; ?></td>
                                <td><?php echo $u['username']; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning">Edit</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>