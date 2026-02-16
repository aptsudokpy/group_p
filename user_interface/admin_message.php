<?php 
ob_start();
require_once '../config/db.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ดึงข้อความจาก Admin
$stmt = $conn->prepare("SELECT admin_message FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$msg = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INCOMING MESSAGE - Group P</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-dark: #0a1120;
            --card-navy: #111b2d;
            --accent-red: #ef4444;
            --accent-blue: #3b82f6;
            --text-white: #ffffff;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-white);
            font-family: 'Kanit', sans-serif;
            min-height: 100vh;
        }

        .font-tech { font-family: 'Orbitron', sans-serif; letter-spacing: 1px; }

        /* Navbar ทึบแน่น */
        .navbar {
            background-color: var(--card-navy) !important;
            border-bottom: 2px solid var(--accent-red);
        }

        /* การ์ดข้อความแจ้งเตือน */
        .message-card {
            background: var(--card-navy);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 20px;
            box-shadow: 0 0 30px rgba(239, 68, 68, 0.15);
            overflow: hidden;
        }

        .card-header-custom {
            background: linear-gradient(90deg, #7f1d1d, #111b2d);
            padding: 20px;
            border-bottom: 1px solid var(--accent-red);
        }

        /* กล่องข้อความแบบ Terminal */
        .msg-display {
            background: #070b14;
            border-left: 4px solid var(--accent-red);
            padding: 25px;
            border-radius: 10px;
            color: #ffffff !important;
            font-size: 1.1rem;
            line-height: 1.6;
            box-shadow: inset 0 0 15px rgba(0,0,0,0.5);
        }

        /* ปุ่มย้อนกลับสไตล์ Uiverse ผสม Bootstrap */
        .btn-back {
            background: transparent;
            color: var(--text-white);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 10px 30px;
            border-radius: 50px;
            transition: 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-back:hover {
            background: var(--text-white);
            color: var(--bg-dark);
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
        }

        .status-ok {
            color: #10b981;
            text-shadow: 0 0 10px rgba(16, 185, 129, 0.5);
        }

        .scan-line {
            width: 100%;
            height: 2px;
            background: rgba(239, 68, 68, 0.2);
            margin-bottom: 20px;
            animation: scan 3s infinite linear;
        }

        @keyframes scan {
            0% { opacity: 0.2; }
            50% { opacity: 1; }
            100% { opacity: 0.2; }
        }
        /* --- ปรับแต่งตัวหนังสือให้สว่างตามที่สั่ง --- */

        /* 1. ส่วนตารางรายการสินค้า (image_6d0c26.png) */
        .table thead th {
            color: #ffffff !important; /* หัวข้อตาราง สินค้า/ราคา/จำนวน ให้ขาวสว่าง */
            background-color: #1e293b; /* พื้นหลังหัวตารางให้ทึบและตัดกับตัวหนังสือ */
            font-weight: 600;
        }

        /* 2. ส่วนยอดรวมสุทธิ / Total Investment (image_6d0c26, image_6d66ff) */
        .total-amount, .total-investment {
            color: #60a5fa !important; /* สีฟ้าสว่าง (Light Blue) ให้เด่น */
            font-weight: 700;
            font-size: 1.25rem;
            text-shadow: 0 0 10px rgba(96, 165, 250, 0.3); /* เพิ่มรัศมีจางๆ ให้ตัวหนังสือดูสว่าง */
        }

        /* 3. ส่วนข้อความความลับ/หมายเหตุ (image_6d1047.png) */
        .alert-info-custom {
            color: #e2e8f0 !important; /* ปรับจากเทามืด เป็นเทาสว่างเกือบขาว */
            background: rgba(15, 23, 42, 0.8);
            border: 1px dashed rgba(59, 130, 246, 0.5);
        }

        /* 4. ส่วน Security / เปลี่ยนรหัสผ่าน (image_6d7d62.png) */
        .security-header {
            color: #f87171 !important; /* สีแดงสว่าง (Light Red) สำหรับหัวข้อ Security */
            text-shadow: 0 0 5px rgba(248, 113, 113, 0.4);
        }

        /* 5. ส่วนสถานะบัญชี (image_6e56a3.png) */
        .status-safe-text {
            color: #4ade80 !important; /* สีเขียวสว่าง (Neon Green) */
            font-weight: 600;
        }

        /* 6. ตัวหนังสือทั่วไปที่เคยจาง (text-muted) ให้สว่างขึ้น */
        .text-muted, .small-text-muted {
            color: #94a3b8 !important; /* ปรับให้เป็นสีเทาสว่างที่อ่านออกง่าย */
        }

        /* 7. บังคับให้ Placeholder ในช่องกรอกสว่างขึ้นนิดนึง (image_6dee5f) */
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6) !important; /* ขาว 60% อ่านง่ายขึ้น */
        }
    </style>
</head>
<body>

    <?php include '../includes/navbar.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="message-card">
                    <div class="card-header-custom">
                        <h4 class="mb-0 font-tech text-white">
                            <i class="fas fa-satellite-dish me-2 animate-pulse"></i> 
                            INCOMING SIGNAL / ข้อความจากAdmin
                        </h4>
                    </div>
                    
                    <div class="card-body p-5">
                        <div class="scan-line"></div>

                        <?php if(!empty($msg)): ?>
                            <div class="text-start">
                                <h6 class="font-tech text-danger mb-3">PRIORITY: HIGH ALERT</h6>
                                <div class="msg-display">
                                    <h5 class="fw-bold mb-3" style="color: var(--accent-red);">[ TRANSMISSION START ]</h5>
                                    <p class="mb-0" style="white-space: pre-wrap;"><?php echo htmlspecialchars($msg); ?></p>
                                    <h5 class="fw-bold mt-3 text-end" style="color: var(--accent-red);">[ END OF MESSAGE ]</h5>
                                </div>
                                
                                <div class="mt-4 p-3 bg-dark rounded border border-secondary border-opacity-25">
                                    <small class="text-secondary font-tech">INSTRUCTION:</small>
                                    <p class="text-muted small mb-0 mt-1">
                                        กรุณาปฏิบัติตามคำสั่งของผู้ดูแลระบบอย่างเคร่งครัด หากมีข้อโต้แย้งให้ติดต่อแผนกเทคนิคผ่านช่องทางสื่อสารหลัก
                                    </p>
                                </div>
                            </div>

                        <?php else: ?>
                            <div class="py-4 text-center">
                                <div class="mb-4">
                                    <i class="fas fa-shield-check fa-5x status-ok"></i>
                                </div>
                                <h2 class="font-tech status-ok">SYSTEM NORMAL</h2>
                                <h5 class="text-white-50">ไม่พบข้อความแจ้งเตือนในขณะนี้</h5>
                                <p class="text-muted mt-3">บัญชีของคุณอยู่ในสถานะปลอดภัยและไม่มีรายงานความประพฤติ</p>
                            </div>
                        <?php endif; ?>

                        <div class="text-center mt-5">
                            <hr class="mb-4 opacity-10">
                            <a href="index.php" class="btn-back font-tech">
                                <i class="fas fa-arrow-left me-2"></i> RETURN TO MAIN BASE
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>