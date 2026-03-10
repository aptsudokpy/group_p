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

// แสดง alert เตือน session variable
if (isset($_SESSION['need_login_alert']) && $_SESSION['need_login_alert']) {
    echo "<script>
        alert('คุณต้อง ล็อกอินก่อนทำการเพิ่มสินค้าลงตะกร้า');
    </script>";
    unset($_SESSION['need_login_alert']);
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
    <link rel="stylesheet" href="../assets/cyberpunk-theme.css">

    <style>
        :root {
            --bg-dark: #050a14; 
            --navy-card: #0d1626;
            --cyber-red: #ff3333;
            --cyber-blue: #00f2ff;
            --text-bright: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--bg-dark);
            background-image: 
                linear-gradient(rgba(0, 242, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 242, 255, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            font-family: 'Kanit', sans-serif;
            color: var(--text-bright);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
        }

        .navbar {
            display: none !important;
        }

        .login-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
            perspective: 1000px;
        }

        .login-card {
            position: relative;
            width: 100%;
            background: linear-gradient(135deg, var(--navy-card) 0%, #0f1b2e 100%);
            border: 2px solid var(--cyber-blue);
            border-radius: 8px;
            padding: 50px 40px;
            box-shadow: 0 10px 40px rgba(0, 242, 255, 0.1), inset 0 0 20px rgba(0, 242, 255, 0.05);
            transition: all 0.4s ease;
            animation: cardGlow 3s ease-in-out infinite;
        }

        @keyframes cardGlow {
            0%, 100% {
                box-shadow: 0 10px 40px rgba(0, 242, 255, 0.1), inset 0 0 20px rgba(0, 242, 255, 0.05);
            }
            50% {
                box-shadow: 0 15px 50px rgba(0, 242, 255, 0.2), inset 0 0 30px rgba(0, 242, 255, 0.1);
            }
        }

        .login-card:hover {
            transform: translateY(-5px);
            border-color: var(--cyber-red);
            box-shadow: 0 15px 50px rgba(255, 51, 51, 0.2), inset 0 0 20px rgba(0, 242, 255, 0.05);
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
        }

        .login-header .logo {
            font-family: 'Orbitron', sans-serif;
            font-size: 32px;
            font-weight: 800;
            letter-spacing: 4px;
            background: linear-gradient(135deg, var(--cyber-blue), var(--cyber-red));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
            text-shadow: 0 0 20px rgba(0, 242, 255, 0.3);
        }

        .login-header .subtitle {
            font-size: 12px;
            letter-spacing: 3px;
            color: rgba(0, 242, 255, 0.7);
            text-transform: uppercase;
        }

        .login-form {
            opacity: 1;
            transform: translateY(0);
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 2px;
            color: rgba(0, 242, 255, 0.8);
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .login-input {
            width: 100%;
            background: rgba(15, 27, 46, 0.8) !important;
            border: 2px solid rgba(0, 242, 255, 0.3) !important;
            border-radius: 4px;
            color: var(--cyber-blue) !important;
            padding: 14px 16px;
            font-size: 14px;
            font-family: 'Kanit', sans-serif;
            transition: all 0.3s ease;
        }

        .login-input:focus {
            border-color: var(--cyber-blue) !important;
            box-shadow: 0 0 15px rgba(0, 242, 255, 0.3), inset 0 0 10px rgba(0, 242, 255, 0.05) !important;
            background: rgba(15, 27, 46, 1) !important;
            outline: none;
        }

        .login-input::placeholder {
            color: rgba(0, 242, 255, 0.4) !important;
        }

        .alert-error {
            background: rgba(255, 51, 51, 0.15);
            color: #ff6b6b;
            border: 2px solid var(--cyber-red);
            border-radius: 4px;
            font-size: 13px;
            padding: 12px 15px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
            animation: shake 0.3s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .login-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--cyber-red), #ff5555);
            color: #fff;
            border: 2px solid var(--cyber-red);
            border-radius: 4px;
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: left 0.3s ease;
            z-index: -1;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255, 51, 51, 0.3);
            border-color: var(--text-bright);
        }

        .login-btn:hover::before {
            left: 100%;
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .back-btn {
            width: 100%;
            padding: 16px;
            background: transparent;
            color: var(--cyber-blue);
            border: 2px solid var(--cyber-blue);
            border-radius: 4px;
            font-family: 'Kanit', sans-serif;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-decoration: none;
            display: inline-block;
            margin-top: 12px;
        }

        .back-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(0, 242, 255, 0.2);
            transition: left 0.3s ease;
            z-index: -1;
        }

        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 242, 255, 0.3);
            border-color: var(--cyber-red);
            color: var(--cyber-red);
        }

        .back-btn:hover::before {
            left: 100%;
        }

        .back-btn:active {
            transform: translateY(0);
        }

        .login-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(0, 242, 255, 0.1);
        }

        .login-footer a {
            color: var(--cyber-blue);
            text-decoration: none;
            font-size: 12px;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .login-footer a:hover {
            color: var(--cyber-red);
            text-shadow: 0 0 10px rgba(255, 51, 51, 0.5);
        }

        .bg-deco-text {
            position: fixed;
            font-family: 'Orbitron', sans-serif;
            font-size: 15vw;
            color: rgba(0, 242, 255, 0.02);
            z-index: -1;
            user-select: none;
            pointer-events: none;
        }

        @media (max-width: 600px) {
            .login-container {
                max-width: 100%;
            }

            .login-card {
                padding: 40px 25px;
            }

            .login-header .logo {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>

    <div class="bg-deco-text" style="top: 5%; left: -5%;">LOG</div>
    <div class="bg-deco-text" style="bottom: 5%; right: -5%;">IN</div>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo">
                    <i class="fas fa-lock me-2" style="color: var(--cyber-blue);"></i>AMMO
                </div>
                <div class="subtitle">SECURE ACCESS</div>
            </div>

            <div class="login-form">
                <?php if(isset($error)): ?>
                    <div class="alert-error">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-user me-2"></i>USERNAME
                        </label>
                        <input type="text" name="username" class="form-control login-input" placeholder="Enter your username" required autocomplete="username">
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-key me-2"></i>PASSWORD
                        </label>
                        <input type="password" name="password" class="form-control login-input" placeholder="Enter your password" required autocomplete="current-password">
                    </div>
                    
                    <button type="submit" name="login" class="login-btn">
                        <i class="fas fa-sign-in-alt me-2"></i>LOGIN NOW
                    </button>

                    <a href="index.php" class="back-btn">
                        <i class="fas fa-home me-2"></i>BACK TO SHOP
                    </a>

                    <div class="login-footer">
                        <span style="color: rgba(0, 242, 255, 0.5); font-size: 11px;">ยังไม่มีบัญชี?</span><br>
                        <a href="register.php">
                            <i class="fas fa-user-plus me-1"></i>สร้างบัญชีใหม่
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>