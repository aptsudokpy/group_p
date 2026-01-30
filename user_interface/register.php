<?php 
require_once '../config/db.php'; // เชื่อมต่อ DB แบบถอยหลัง 1 ขั้น

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // เข้ารหัสรหัสผ่าน
    $fullname = $_POST['fullname'];
    $address  = $_POST['address'];

    // เช็คว่า Username ซ้ำไหม
    $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check->execute([$username]);
    
    if ($check->rowCount() > 0) {
        $error = "ชื่อผู้ใช้นี้ถูกใช้งานแล้ว";
    } else {
        // บันทึกข้อมูล
        $sql = "INSERT INTO users (username, password, fullname, address, role) VALUES (?, ?, ?, ?, 'user')";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$username, $password, $fullname, $address])) {
            header("Location: login.php"); // สมัครเสร็จเด้งไปหน้า Login
            exit();
        } else {
            $error = "เกิดข้อผิดพลาด โปรดลองใหม่";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    
    <?php include '../includes/navbar.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h4 class="mb-0">📝 สมัครสมาชิกใหม่</h4>
                    </div>
                    <div class="card-body">
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form action="register.php" method="POST">
                            <div class="mb-3">
                                <label>ชื่อผู้ใช้ (Username)</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>รหัสผ่าน (Password)</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>ชื่อ-นามสกุล</label>
                                <input type="text" name="fullname" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>ที่อยู่จัดส่ง</label>
                                <textarea name="address" class="form-control" rows="3"></textarea>
                            </div>
                            <button type="submit" name="register" class="btn btn-warning w-100">ยืนยันการสมัคร</button>
                        </form>
                        <div class="mt-3 text-center">
                            มีบัญชีแล้ว? <a href="login.php">เข้าสู่ระบบที่นี่</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>