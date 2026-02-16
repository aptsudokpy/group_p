<?php 
session_start();
ob_start();
require_once '../config/db.php'; 

// ตรวจสอบว่าถ้าล็อกอินอยู่แล้ว ให้เด้งไปหน้า index ทันที
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// --- ส่วนที่เพิ่มเข้ามา: ระบบตรวจสอบการล็อกอิน ---
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        // ดึงข้อมูลผู้ใช้จากชื่อผู้ใช้
        $check = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $check->execute([$username]);
        $row = $check->fetch(PDO::FETCH_ASSOC);

        if ($check->rowCount() > 0) {
            // ตรวจสอบรหัสผ่านที่ Hash ไว้
            if (password_verify($password, $row['password'])) {
                // เก็บข้อมูลลง Session
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];

                // ล็อกอินสำเร็จ เด้งไปหน้า index.php
                header("Location: index.php");
                exit();
            } else {
                $error = "รหัสผ่านไม่ถูกต้อง";
            }
        } else {
            $error = "ไม่พบชื่อผู้ใช้นี้ในระบบ";
        }
    } catch(PDOException $e) {
        $error = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IDENTIFICATION - Group P</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;800&family=Orbitron:wght@700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-dark: #050a14; 
            --navy-card: #0d1626;
            --cyber-red: #ff3333;
            --cyber-blue: #00f2ff;
            --text-bright: #ffffff;
        }

        body {
            background-color: var(--bg-dark);
            background-image: 
                linear-gradient(rgba(0, 242, 255, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 242, 255, 0.05) 1px, transparent 1px);
            background-size: 30px 30px;
            font-family: 'Kanit', sans-serif;
            color: var(--text-bright);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin: 0;
        }

        /* 1. Navbar: ลบแถบออกตามคำขอก่อนหน้า */
        .navbar {
            display: none !important;
        }

        /* 2. Login Container & Hover Effect */
        .login-container {
            position: relative;
            perspective: 1000px;
            width: 400px;
        }

        .login-card {
            position: relative;
            width: 100%;
            height: 100px;
            background: var(--navy-card);
            border: 3px solid #000;
            box-shadow: 8px 8px 0px #000;
            overflow: hidden;
            transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            transform-style: preserve-3d;
        }

        .login-card:hover {
            height: 520px; /* เพิ่มพื้นที่สำหรับแจ้งเตือน Error */
            transform: translateZ(20px);
            box-shadow: 15px 15px 0px var(--cyber-blue), 0 0 30px rgba(0, 242, 255, 0.2);
            border-color: var(--cyber-blue);
        }

        .login-title {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100px;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #0d1626, #16253d);
            transition: 0.4s;
            z-index: 2;
        }

        .login-text {
            color: var(--text-bright);
            font-family: 'Orbitron', sans-serif;
            font-size: 22px;
            letter-spacing: 4px;
            text-shadow: 0 0 10px var(--cyber-blue);
        }

        .login-card:hover .login-title {
            background: var(--cyber-blue);
            height: 80px;
        }

        .login-card:hover .login-text {
            color: #000;
            font-size: 18px;
            text-shadow: none;
        }

        .login-form {
            position: absolute;
            top: 100px;
            left: 0;
            width: 100%;
            padding: 30px;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s ease 0.2s;
        }

        .login-card:hover .login-form {
            opacity: 1;
            transform: translateY(-20px);
        }

        .login-input {
            background: #050a14 !important;
            border: 2px solid #1e293b !important;
            color: var(--cyber-blue) !important;
            border-radius: 0;
            margin-bottom: 20px;
            padding: 12px;
            font-weight: bold;
        }

        .login-btn {
            width: 100%;
            padding: 15px;
            background: var(--cyber-red);
            color: #fff;
            border: none;
            font-family: 'Orbitron', sans-serif;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
            transition: 0.3s;
            box-shadow: 4px 4px 0px #000;
        }

        .login-btn:hover {
            background: #fff;
            color: #000;
            box-shadow: 6px 6px 0px var(--cyber-blue);
        }

        .alert-error {
            background: rgba(255, 51, 51, 0.1);
            color: #ff8080;
            border: 1px solid var(--cyber-red);
            font-size: 13px;
            text-align: center;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 0;
        }

        .bg-deco-text {
            position: absolute;
            font-family: 'Orbitron';
            font-size: 12vw;
            color: rgba(0, 242, 255, 0.02);
            z-index: -1;
            user-select: none;
        }
    </style>
</head>
<body>

    <div class="bg-deco-text" style="top: 10%; left: 5%;">LOG-IN</div>
    <div class="bg-deco-text" style="bottom: 10%; right: 5%;">SECURE</div>

    <div class="login-container">
        <div class="login-card">
            <div class="login-title">
                <span class="login-text"><i class="fas fa-shield-alt me-2"></i> ACCESS SYSTEM</span>
            </div>

            <div class="login-form">
                <?php if(isset($error)): ?>
                    <div class="alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div class="mb-3">
                        <label class="small text-white-50 mb-1">IDENTIFICATION</label>
                        <input type="text" name="username" class="form-control login-input" placeholder="ENTER USERNAME" required>
                    </div>
                    <div class="mb-4">
                        <label class="small text-white-50 mb-1">SECURITY CODE</label>
                        <input type="password" name="password" class="form-control login-input" placeholder="••••••••" required>
                    </div>
                    
                    <button type="submit" name="login" class="login-btn">
                        EXECUTE LOGIN
                    </button>

                    <div class="text-center mt-4">
                        <a href="register.php" class="text-decoration-none small" style="color: var(--cyber-blue); opacity: 0.7;">
                            CREATE NEW OPERATOR ACCOUNT ยังไม่มีบัญชี
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>