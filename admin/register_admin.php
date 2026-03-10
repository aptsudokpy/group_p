<?php 
require_once '../config/db.php'; 

// --- ตั้งค่ารหัสลับสำหรับสมัคร Admin (แก้ได้ตามใจชอบ) ---
$SECRET_KEY = "admin1234"; 

if (isset($_POST['register_admin'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $fullname = $_POST['fullname'];
    $input_secret = $_POST['secret_key']; // รับค่ารหัสลับ

    // 1. ตรวจสอบรหัสลับร้านค้าก่อน
    if ($input_secret !== $SECRET_KEY) {
        $error = "❌ รหัสลับร้านค้าไม่ถูกต้อง! (คุณไม่มีสิทธิ์สมัคร Admin)";
    } else {
        // 2. ตรวจสอบจำนวน Admin ปัจจุบัน
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        $stmt->execute();
        $admin_count = $stmt->fetchColumn();

        if ($admin_count >= 5) {
            $error = "❌ ขออภัย! จำนวนแอดมินเต็มแล้ว (จำกัด 5 ท่าน)";
        } else {
            // 3. ตรวจสอบชื่อซ้ำ
            $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $check->execute([$username]);
            
            if ($check->rowCount() > 0) {
                $error = "ชื่อผู้ใช้นี้ถูกใช้งานแล้ว";
            } else {
                // 4. บันทึกข้อมูล (Role = admin)
                $hash_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (username, password, fullname, role) VALUES (?, ?, ?, 'admin')";
                $stmt = $conn->prepare($sql);
                
                if ($stmt->execute([$username, $hash_password, $fullname])) {
                    $success = "✅ สมัคร Admin สำเร็จ! (คนที่ " . ($admin_count + 1) . "/5)";
                    // แสดง Alert แจ้งเตือนแล้วเด้งไปหน้า Login
                    echo "<script>
                        alert('🎉 สมัครบัญชีเสร็จสิ้น!\\n\\nยินดีต้อนรับเข้าสู่ระบบ Admin\\nกรุณากรอกข้อมูลเข้าสู่ระบบ');
                        window.location.href='../user_interface/login.php';
                    </script>";
                    exit();
                } else {
                    $error = "เกิดข้อผิดพลาด โปรดลองใหม่";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>สมัครผู้ดูแลระบบ (Admin) - จำกัด 5 ท่าน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark d-flex align-items-center vh-100">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card border-0 shadow-lg">
                    <div class="card-header bg-danger text-white text-center py-3">
                        <h4 class="mb-0">👮 สมัครผู้ดูแลระบบ (Admin)</h4>
                    </div>
                    <div class="card-body p-4">
                        
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if(isset($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>

                        <form action="register_admin.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-danger">🔑 รหัสลับร้านค้า (Secret Key)</label>
                                <input type="password" name="secret_key" class="form-control border-danger" placeholder="กรอกรหัสลับเพื่อยืนยันตัวตน" required>
                                <small class="text-muted">เฉพาะผู้ได้รับอนุญาตเท่านั้น</small>
                            </div>
                            <hr>
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">ชื่อแอดมิน (Fullname)</label>
                                <input type="text" name="fullname" class="form-control" required>
                            </div>

                            <button type="submit" name="register_admin" class="btn btn-danger w-100 mb-3">ลงทะเบียน Admin</button>
                        </form>
                        
                        <div class="text-center">
                            <a href="login_admin.php" class="text-secondary">มีบัญชี Admin แล้ว? เข้าสู่ระบบ</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>