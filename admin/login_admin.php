<?php 
require_once '../config/db.php'; 

if (isset($_POST['login_admin'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        // เช็ค username และต้องเป็น role 'admin' เท่านั้น
        $check = $conn->prepare("SELECT * FROM users WHERE username = ? AND role = 'admin'");
        $check->execute([$username]);
        $row = $check->fetch(PDO::FETCH_ASSOC);

        if ($check->rowCount() > 0 && password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role']; // admin
            
            header("Location: index.php"); // ไปหน้า Dashboard
            exit();
        } else {
            $error = "❌ เข้าสู่ระบบไม่สำเร็จ (คุณไม่ใช่ Admin หรือรหัสผิด)";
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
    <title>เข้าสู่ระบบหลังบ้าน (Admin Login)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-secondary d-flex align-items-center vh-100">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5 text-center">
                        <h3 class="fw-bold mb-4">🔐 Admin Login</h3>
                        
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger text-start"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form action="login_admin.php" method="POST">
                            <div class="form-floating mb-3">
                                <input type="text" name="username" class="form-control" id="floatingInput" placeholder="Username" required>
                                <label for="floatingInput">Admin Username</label>
                            </div>
                            <div class="form-floating mb-4">
                                <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Password" required>
                                <label for="floatingPassword">Password</label>
                            </div>
                            <button type="submit" name="login_admin" class="btn btn-dark w-100 py-2">เข้าสู่ระบบหลังบ้าน</button>
                        </form>

                        <div class="mt-4">
                            <a href="register_admin.php" class="text-muted small">สมัคร Admin ใหม่ (ต้องมีรหัสลับ)</a>
                        </div>
                        <div class="mt-2">
                            <a href="../user_interface/login.php" class="text-decoration-none small">← ไปหน้าลูกค้าทั่วไป</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>