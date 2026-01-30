<?php 
require_once '../config/db.php'; // เรียกใช้ DB แบบถอยหลัง 1 ขั้น

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        // ค้นหา user จาก database
        $check = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $check->execute([$username]);
        $row = $check->fetch(PDO::FETCH_ASSOC);

        // ถ้าเจอยูสเซอร์ และ รหัสผ่านถูกต้อง (Verify Hash)
        if ($check->rowCount() > 0 && password_verify($password, $row['password'])) {
            
            // สร้าง Session เก็บข้อมูลคนล็อกอิน
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['fullname'] = $row['fullname'];

            // เช็ค Role เพื่อพาไปหน้าที่ถูกต้อง
            if ($row['role'] == 'admin') {
                header("Location: ../admin/index.php"); // ถ้าเป็น Admin ไปหลังบ้าน
            } else {
                header("Location: index.php"); // ถ้าเป็น User ทั่วไป ไปหน้าแรก
            }
            exit();

        } else {
            $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - Group P</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <?php include '../includes/navbar.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow border-0">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h3 class="fw-bold text-primary">🔐 เข้าสู่ระบบ</h3>
                            <p class="text-muted">ยินดีต้อนรับกลับสู่ Group P Shop</p>
                        </div>

                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger text-center"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form action="login.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label">ชื่อผู้ใช้ (Username)</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">รหัสผ่าน (Password)</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" name="login" class="btn btn-primary w-100 py-2">เข้าสู่ระบบ</button>
                        </form>

                        <hr class="my-4">
                        
                        <div class="text-center">
                            ยังไม่มีบัญชี? <a href="register.php" class="text-decoration-none fw-bold">สมัครสมาชิกที่นี่</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>