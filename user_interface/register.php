<?php 
require_once '../config/db.php'; 

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $fullname = $_POST['fullname'];
    $address  = $_POST['address'];

    $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check->execute([$username]);
    
    if ($check->rowCount() > 0) {
        $error = "ชื่อผู้ใช้นี้ถูกใช้งานแล้วในระบบ";
    } else {
        $sql = "INSERT INTO users (username, password, fullname, address, role) VALUES (?, ?, ?, ?, 'user')";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$username, $password, $fullname, $address])) {
            header("Location: login.php");
            exit();
        } else {
            $error = "เกิดข้อผิดพลาดในการเชื่อมต่อวงจร โปรดลองใหม่";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REGISTRATION - Group P</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;800&family=Orbitron:wght@700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-dark: #050a14;
            --navy-card: #0d1626;
            --cyber-blue: #00f2ff;
            --cyber-red: #ff3333;
            --text-bright: #ffffff;
        }

        body {
            background-color: var(--bg-dark);
            background-image: 
                linear-gradient(rgba(0, 242, 255, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 242, 255, 0.05) 1px, transparent 1px);
            background-size: 40px 40px;
            font-family: 'Kanit', sans-serif;
            color: var(--text-bright);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0; /* เพิ่มเพื่อให้ชิดขอบ */
        }

        /* --- ส่วนที่แก้ไข: ลบแถบ Navbar ออก --- */
        .navbar, nav {
            display: none !important;
        }

        .reg-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px; /* ปรับ padding ให้สมดุลเมื่อไม่มี navbar */
        }
        /* ---------------------------------- */

        .reg-card {
            width: 100%;
            max-width: 550px;
            background: var(--navy-card);
            border: 4px solid #000;
            box-shadow: 12px 12px 0px #000;
            position: relative;
        }

        .reg-header {
            background: var(--cyber-blue);
            padding: 20px;
            border-bottom: 4px solid #000;
            text-align: center;
        }

        .reg-header h4 {
            font-family: 'Orbitron', sans-serif;
            color: #000;
            font-weight: 800;
            margin: 0;
            letter-spacing: 2px;
        }

        .reg-body {
            padding: 40px;
        }

        .form-label {
            font-size: 13px;
            color: var(--cyber-blue);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .reg-input {
            background: #050a14 !important;
            border: 2px solid #1e293b !important;
            color: #fff !important;
            border-radius: 0;
            padding: 12px;
            margin-bottom: 20px;
            transition: 0.3s;
        }

        .reg-input:focus {
            border-color: var(--cyber-blue) !important;
            box-shadow: 0 0 15px rgba(0, 242, 255, 0.2);
            outline: none;
        }

        .btn-register {
            background: var(--cyber-red);
            color: #fff;
            border: 3px solid #000;
            font-family: 'Orbitron', sans-serif;
            font-weight: 800;
            padding: 15px;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 2px;
            box-shadow: 6px 6px 0px #000;
            transition: 0.3s;
        }

        .btn-register:hover {
            background: #fff;
            color: #000;
            transform: translate(-3px, -3px);
            box-shadow: 9px 9px 0px var(--cyber-blue);
        }

        .alert-custom {
            background: rgba(255, 51, 51, 0.2);
            border: 1px solid var(--cyber-red);
            color: #ff8080;
            border-radius: 0;
            font-size: 14px;
            text-align: center;
            margin-bottom: 20px;
        }

        .login-link {
            color: var(--cyber-blue);
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
        }

        .login-link:hover {
            color: #fff;
            text-shadow: 0 0 10px var(--cyber-blue);
        }

        .reg-card::after {
            content: "GROUP-P-SECURE";
            position: absolute;
            bottom: -30px;
            right: 0;
            font-family: 'Orbitron';
            font-size: 10px;
            color: rgba(255,255,255,0.2);
        }
    </style>
</head>
<body>

    <?php include '../includes/navbar.php'; ?>

    <div class="reg-wrapper">
        <div class="reg-card">
            <div class="reg-header">
                <h4><i class="fas fa-user-plus me-2"></i> NEW OPERATOR</h4>
            </div>

            <div class="reg-body">
                <?php if(isset($error)): ?>
                    <div class="alert alert-custom"><?php echo $error; ?></div>
                <?php endif; ?>

                <form action="register.php" method="POST">
                    <div class="row">
                        <div class="col-md-12">
                            <label class="form-label">Username / รหัสเข้าใช้งาน</label>
                            <input type="text" name="username" class="form-control reg-input" placeholder="REQUIRED" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Password / รหัสผ่านลับ</label>
                            <input type="password" name="password" class="form-control reg-input" placeholder="••••••••" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Full Name / ชื่อ-นามสกุล</label>
                            <input type="text" name="fullname" class="form-control reg-input" placeholder="ENTER FULL NAME" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Shipping Address / ที่อยู่จัดส่งยุทธภัณฑ์</label>
                            <textarea name="address" class="form-control reg-input" rows="3" placeholder="COMPLETE ADDRESS..."></textarea>
                        </div>
                    </div>

                    <button type="submit" name="register" class="btn-register mt-3">
                        CONFIRM REGISTRATION
                    </button>
                </form>

                <div class="mt-4 text-center">
                    <span class="text-white-50 small">ALREADY REGISTERED?</span> 
                    <a href="login.php" class="login-link ms-2 small">ACCESS SYSTEM</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>