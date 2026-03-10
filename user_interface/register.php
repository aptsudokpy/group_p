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
            // แสดง Alert แจ้งเตือนแล้วเด้งไปหน้า Login
            echo "<script>
                alert('🎉 สมัครบัญชีเสร็จสิ้น!\\n\\nยินดีต้อนรับเข้าสู่สินค้อน\\nกรุณากรอกข้อมูลเข้าสู่ระบบ');
                window.location.href='login.php';
            </script>";
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
    <link rel="stylesheet" href="../assets/cyberpunk-theme.css">

    <style>
        :root {
            --bg-dark: #050a14;
            --navy-card: #0d1626;
            --cyber-blue: #00f2ff;
            --cyber-red: #ff3333;
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
            padding-top: 80px;
        }

        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1030;
        }

        .reg-wrapper {
            width: 100%;
            max-width: 500px;
            padding: 20px;
            margin: auto;
        }

        .reg-card {
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

        .reg-card:hover {
            transform: translateY(-5px);
            border-color: var(--cyber-red);
            box-shadow: 0 15px 50px rgba(255, 51, 51, 0.2), inset 0 0 20px rgba(0, 242, 255, 0.05);
        }

        .reg-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .reg-header .logo {
            font-family: 'Orbitron', sans-serif;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: 4px;
            background: linear-gradient(135deg, var(--cyber-blue), var(--cyber-red));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }

        .reg-header .subtitle {
            font-size: 12px;
            letter-spacing: 3px;
            color: rgba(0, 242, 255, 0.7);
            text-transform: uppercase;
        }

        .reg-body {
            opacity: 1;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 2px;
            color: rgba(0, 242, 255, 0.8);
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .reg-input {
            width: 100%;
            background: rgba(15, 27, 46, 0.8) !important;
            border: 2px solid rgba(0, 242, 255, 0.3) !important;
            border-radius: 4px;
            color: var(--cyber-blue) !important;
            padding: 12px 14px;
            font-size: 13px;
            font-family: 'Kanit', sans-serif;
            transition: all 0.3s ease;
        }

        .reg-input:focus {
            border-color: var(--cyber-blue) !important;
            box-shadow: 0 0 15px rgba(0, 242, 255, 0.3), inset 0 0 10px rgba(0, 242, 255, 0.05) !important;
            background: rgba(15, 27, 46, 1) !important;
            outline: none;
        }

        .reg-input::placeholder {
            color: rgba(0, 242, 255, 0.4) !important;
        }

        .alert-custom {
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

        .btn-register {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--cyber-red), #ff5555);
            color: #fff;
            border: 2px solid var(--cyber-red);
            border-radius: 4px;
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-register::before {
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

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255, 51, 51, 0.3);
            border-color: var(--text-bright);
        }

        .btn-register:hover::before {
            left: 100%;
        }

        .reg-footer {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid rgba(0, 242, 255, 0.1);
        }

        .reg-footer a {
            color: var(--cyber-blue);
            text-decoration: none;
            font-size: 12px;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .reg-footer a:hover {
            color: var(--cyber-red);
            text-shadow: 0 0 10px rgba(255, 51, 51, 0.5);
        }

        @media (max-width: 600px) {
            body {
                padding-top: 60px;
            }

            .reg-card {
                padding: 35px 25px;
            }

            .reg-header .logo {
                font-size: 22px;
            }
        }
    </style>

</head>
<body>

    <?php include '../includes/navbar.php'; ?>

    <div class="reg-wrapper">
        <div class="reg-card">
            <div class="reg-header">
                <div class="logo">
                    <i class="fas fa-user-plus me-2" style="color: var(--cyber-blue);"></i>REGISTER
                </div>
                <div class="subtitle">CREATE NEW ACCOUNT</div>
            </div>

            <div class="reg-body">
                <?php if(isset($error)): ?>
                    <div class="alert-custom">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form action="register.php" method="POST">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-user me-2"></i>USERNAME
                        </label>
                        <input type="text" name="username" class="form-control reg-input" placeholder="Choose your username" required autocomplete="username">
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-lock me-2"></i>PASSWORD
                        </label>
                        <input type="password" name="password" class="form-control reg-input" placeholder="Create a strong password" required autocomplete="new-password">
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-id-card me-2"></i>FULLNAME
                        </label>
                        <input type="text" name="fullname" class="form-control reg-input" placeholder="Enter your full name" required autocomplete="name">
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-map-marker-alt me-2"></i>ADDRESS
                        </label>
                        <textarea name="address" class="form-control reg-input" rows="3" placeholder="Enter your complete address" style="resize: none;"></textarea>
                    </div>

                    <button type="submit" name="register" class="btn-register">
                        <i class="fas fa-check me-2"></i>REGISTER NOW
                    </button>
                </form>

                <div class="reg-footer">
                    <span style="color: rgba(0, 242, 255, 0.5); font-size: 11px;">ALREADY HAVE ACCOUNT?</span><br>
                    <a href="login.php">
                        <i class="fas fa-sign-in-alt me-1"></i>LOGIN HERE
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>