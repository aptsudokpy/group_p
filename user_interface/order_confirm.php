<?php 
require_once '../config/db.php'; 

// --- Logic เดิมคงไว้ทั้งหมด ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$cart = $_SESSION['cart'];
$total_price = 0;

try {
    $conn->beginTransaction();

    foreach ($cart as $p_id => $qty) {
        $stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
        $stmt->execute([$p_id]);
        $product = $stmt->fetch();
        if ($product) {
            $total_price += $product['price'] * $qty;
        }
    }

    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_price, status) VALUES (?, ?, 'pending')");
    $stmt->execute([$user_id, $total_price]);
    $order_id = $conn->lastInsertId();

    $stmt_detail = $conn->prepare("INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($cart as $p_id => $qty) {
        $stmt_product = $conn->prepare("SELECT price FROM products WHERE id = ?");
        $stmt_product->execute([$p_id]);
        $product = $stmt_product->fetch();
        if ($product) {
            $stmt_detail->execute([$order_id, $p_id, $qty, $product['price']]);
        }
    }

    unset($_SESSION['cart']);
    $conn->commit();

} catch (Exception $e) {
    $conn->rollBack();
    echo "เกิดข้อผิดพลาด: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Complete - Weapon Shop</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --success-glow: #10b981;
            --dark-bg: #020617;
            --card-bg: rgba(30, 41, 59, 0.7);
        }

        body {
            background-color: var(--dark-bg);
            color: #f8fafc;
            font-family: 'Kanit', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .font-tech { font-family: 'Orbitron', sans-serif; letter-spacing: 2px; }

        /* --- SUCCESS CARD --- */
        .success-container {
            max-width: 700px;
            margin: 80px auto;
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 30px;
            padding: 50px;
            text-align: center;
            box-shadow: 0 0 50px rgba(16, 185, 129, 0.1);
            position: relative;
            overflow: hidden;
        }

        /* ตกแต่งมุมการ์ดสไตล์ Sci-fi */
        .success-container::before {
            content: "";
            position: absolute;
            top: 0; left: 0;
            width: 40px; height: 40px;
            border-top: 3px solid var(--success-glow);
            border-left: 3px solid var(--success-glow);
        }

        /* Holographic Checkmark */
        .check-circle {
            width: 120px;
            height: 120px;
            background: rgba(16, 185, 129, 0.1);
            border: 4px solid var(--success-glow);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 60px;
            color: var(--success-glow);
            box-shadow: 0 0 30px rgba(16, 185, 129, 0.4);
            animation: pulseSuccess 2s infinite;
        }

        @keyframes pulseSuccess {
            0% { transform: scale(1); box-shadow: 0 0 20px rgba(16, 185, 129, 0.4); }
            50% { transform: scale(1.05); box-shadow: 0 0 40px rgba(16, 185, 129, 0.6); }
            100% { transform: scale(1); box-shadow: 0 0 20px rgba(16, 185, 129, 0.4); }
        }

        .order-id-box {
            background: rgba(0, 0, 0, 0.4);
            border: 1px dashed rgba(255,255,255,0.2);
            padding: 15px;
            border-radius: 10px;
            display: inline-block;
            margin: 20px 0;
        }

        /* Buttons */
        .btn-history {
            background: linear-gradient(45deg, #3b82f6, #2563eb);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            font-family: 'Orbitron', sans-serif;
            font-size: 0.9rem;
            transition: 0.3s;
        }
        .btn-history:hover {
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.5);
            transform: translateY(-2px);
            color: white;
        }

        .btn-armory {
            background: transparent;
            border: 1px solid rgba(255,255,255,0.2);
            color: rgba(255,255,255,0.7);
            padding: 12px 30px;
            border-radius: 50px;
            font-family: 'Orbitron', sans-serif;
            font-size: 0.9rem;
            transition: 0.3s;
            text-decoration: none;
        }
        .btn-armory:hover {
            border-color: white;
            color: white;
            background: rgba(255,255,255,0.05);
        }

        .status-badge {
            font-size: 0.8rem;
            background: rgba(16, 185, 129, 0.2);
            color: var(--success-glow);
            padding: 5px 15px;
            border-radius: 5px;
            border: 1px solid var(--success-glow);
        }
    </style>
</head>
<body>

    <?php include '../includes/navbar.php'; ?>

    <div class="container">
        <div class="success-container shadow-lg">
            
            <div class="check-circle">
                <i class="fas fa-check"></i>
            </div>

            <h1 class="font-tech text-uppercase mb-2" style="color: var(--success-glow);">Transaction Authorized</h1>
            <p class="text-secondary mb-4">การสั่งซื้อถูกบันทึกลงในฐานข้อมูลระบบเรียบร้อยแล้ว</p>

            <div class="order-id-box">
                <span class="text-muted font-tech small d-block mb-1">CONTRACT NO.</span>
                <span class="h4 font-tech text-white">#<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></span>
            </div>

            <div class="mb-5">
                <span class="status-badge font-tech">STATUS: CONFIRMED</span>
            </div>

            <p class="text-muted mb-5">
                สินค้าจะถูกเตรียมจัดส่งไปยังพิกัดของคุณภายใน 24 ชั่วโมง<br>
                ขอบคุณที่เลือกใช้บริการจากหน่วยงานของเรา
            </p>

            <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                <a href="order_history.php" class="btn btn-history">
                    <i class="fas fa-file-invoice me-2"></i> VIEW HISTORY
                </a>
                <a href="index.php" class="btn btn-armory">
                    <i class="fas fa-shopping-cart me-2"></i> RETURN TO ARMORY
                </a>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>