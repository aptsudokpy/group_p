<?php 
session_start(); // อย่าลืม session_start() ถ้าใน db.php ยังไม่มี
require_once '../config/db.php'; 

// --- Logic เดิมของระบบตะกร้า (ไม่แตะต้อง Logic) ---

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// 1. เพิ่มสินค้าใหม่
if (isset($_POST['action']) && $_POST['action'] == 'add') {
    $pid = $_POST['product_id'];
    $qty = $_POST['quantity'];

    if (isset($_SESSION['cart'][$pid])) {
        $_SESSION['cart'][$pid] += $qty;
    } else {
        $_SESSION['cart'][$pid] = $qty;
    }
    header("Location: cart.php");
    exit();
}

// 2. อัปเดตจำนวนสินค้า
if (isset($_GET['action']) && $_GET['action'] == 'update_qty') {
    $pid = $_GET['id'];
    $mode = $_GET['mode']; 

    if (isset($_SESSION['cart'][$pid])) {
        if ($mode == 'increase') {
            $_SESSION['cart'][$pid]++; 
        } elseif ($mode == 'decrease') {
            $_SESSION['cart'][$pid]--; 
            if ($_SESSION['cart'][$pid] <= 0) {
                unset($_SESSION['cart'][$pid]); 
            }
        }
    }
    header("Location: cart.php");
    exit();
}

// 3. ลบสินค้า
if (isset($_GET['action']) && $_GET['action'] == 'remove') {
    $pid = $_GET['id'];
    unset($_SESSION['cart'][$pid]);
    header("Location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Loadout - Weapon Shop</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* --- THEME CONFIG --- */
        :root {
            --primary-glow: #3b82f6;
            --accent-color: #00d2ff;
            --dark-bg: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.6);
            --text-main: #f8fafc;
        }

        body {
            background-color: var(--dark-bg);
            color: var(--text-main);
            font-family: 'Kanit', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .font-tech { font-family: 'Orbitron', sans-serif; letter-spacing: 1px; }

        .navbar, nav {
            background-color: #020617 !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* --- CART SPECIFIC --- */
        .cart-container {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        /* Table Styles */
        .table-custom {
            --bs-table-bg: transparent;
            --bs-table-color: var(--text-main);
            --bs-table-border-color: rgba(255,255,255,0.1);
        }
        
        .table-custom thead th {
            font-family: 'Orbitron', sans-serif;
            color: var(--primary-glow);
            text-transform: uppercase;
            font-size: 0.85rem;
            border-bottom: 2px solid var(--primary-glow);
            padding-bottom: 1rem;
        }

        .table-custom td {
            vertical-align: middle;
            padding: 1.5rem 0.5rem;
        }

        .product-img-thumb {
            width: 70px;
            height: 70px;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.1);
            background: #1e293b;
            padding: 2px;
        }

        /* Quantity Controls */
        .qty-group {
            background: rgba(0,0,0,0.3);
            border-radius: 50px;
            padding: 2px;
            border: 1px solid rgba(255,255,255,0.1);
            display: inline-flex;
            align-items: center;
        }

        .btn-qty {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: none;
            background: rgba(255,255,255,0.05);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
            text-decoration: none;
        }
        .btn-qty:hover {
            background: var(--primary-glow);
            color: white;
        }

        .qty-display {
            width: 40px;
            text-align: center;
            background: transparent;
            border: none;
            color: white;
            font-family: 'Orbitron', sans-serif;
            font-weight: bold;
        }

        /* Summary Panel */
        .summary-card {
            background: rgba(2, 6, 23, 0.6);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 16px;
            position: sticky;
            top: 20px;
        }

        .summary-header {
            background: rgba(59, 130, 246, 0.1);
            border-bottom: 1px solid rgba(59, 130, 246, 0.2);
            padding: 1.5rem;
        }

        .btn-checkout {
            background: linear-gradient(90deg, #10b981, #059669);
            border: none;
            font-family: 'Orbitron', sans-serif;
            letter-spacing: 1px;
            transition: 0.3s;
        }
        .btn-checkout:hover {
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.4);
            transform: translateY(-2px);
        }

        .btn-remove {
            color: #ef4444;
            opacity: 0.7;
            transition: 0.3s;
        }
        .btn-remove:hover {
            opacity: 1;
            transform: scale(1.2);
            text-shadow: 0 0 10px #ef4444;
        }

        .empty-cart-box {
            border: 2px dashed rgba(255,255,255,0.1);
            border-radius: 20px;
            background: rgba(255,255,255,0.02);
        }
        /* --- UIverse Animation Styles --- */
        .pay-btn {
        position: relative;
        padding: 12px 24px;
        font-size: 16px;
        background: linear-gradient(90deg, #10b981, #059669); /* ใช้สีเขียวเดิมของร้าน */
        color: white;
        border: none;
        border-radius: 50px; /* ปรับให้โค้งมนเข้ากับธีมร้าน */
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.3s ease;
        width: 100%; /* ให้เต็มความกว้างกล่องสรุป */
        text-decoration: none;
        font-family: 'Orbitron', sans-serif;
        }

        .pay-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(16, 185, 129, 0.4);
        }

        .icon-container {
        position: relative;
        width: 24px;
        height: 24px;
        }

        .icon {
        position: absolute;
        top: 0;
        left: 0;
        width: 24px;
        height: 24px;
        color: #ffffff; /* เปลี่ยนเป็นสีขาวเพื่อให้ตัดกับพื้นหลังเขียว */
        opacity: 0;
        visibility: hidden;
        }

        .default-icon {
        opacity: 1;
        visibility: visible;
        }

        /* เมื่อ Hover ให้ซ่อนไอคอนเริ่มต้นแล้วแสดงไอคอนติ๊กถูก */
        .pay-btn:hover .default-icon {
        opacity: 0;
        visibility: hidden;
        }

        .pay-btn:hover .check-icon {
        animation: checkmarkAppear 0.6s ease forwards;
        visibility: visible;
        opacity: 1;
        }

        .btn-text {
        font-weight: 600;
        }

        @keyframes checkmarkAppear {
        0% {
            opacity: 0;
            transform: scale(0.5) rotate(-45deg);
        }
        100% {
            opacity: 1;
            transform: scale(1) rotate(0deg);
        }
        }

    </style>
</head>
<body>

    <?php include '../includes/navbar.php'; ?>

    <div class="container mt-5 mb-5">
        <h2 class="mb-4 font-tech text-uppercase">
            <i class="fas fa-boxes-stacked text-primary me-2"></i> Inventory Loadout
        </h2>

        <?php if (empty($_SESSION['cart'])): ?>
            <div class="empty-cart-box text-center p-5 mt-4">
                <i class="fas fa-box-open fa-4x text-muted mb-3 opacity-50"></i>
                <h4 class="font-tech text-muted">YOUR INVENTORY IS EMPTY</h4>
                <p class="text-secondary">Ready to gear up? Check out the armory.</p>
                <a href="index.php" class="btn btn-outline-primary rounded-pill px-4 mt-3 font-tech">
                    GO TO ARMORY
                </a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="cart-container p-4">
                        <div class="table-responsive">
                            <table class="table table-custom mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 45%;">WEAPON MODEL</th>
                                        <th class="text-center">UNIT PRICE</th>
                                        <th class="text-center">QUANTITY</th>
                                        <th class="text-end">SUBTOTAL</th>
                                        <th style="width: 5%;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $total_order = 0;
                                    foreach ($_SESSION['cart'] as $p_id => $qty): 
                                        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
                                        $stmt->execute([$p_id]);
                                        $product = $stmt->fetch();
                                        
                                        if($product):
                                            $subtotal = $product['price'] * $qty;
                                            $total_order += $subtotal;
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="../assets/images/<?php echo !empty($product['image']) ? $product['image'] : 'no-image.jpg'; ?>" 
                                                     class="product-img-thumb object-fit-cover">
                                                <div>
                                                    <h6 class="mb-1 fw-bold text-white"><?php echo $product['name']; ?></h6>
                                                    <small class="text-muted font-tech" style="font-size: 0.75rem;">ID: #<?php echo str_pad($p_id, 4, '0', STR_PAD_LEFT); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center font-tech text-muted">
                                            ฿<?php echo number_format($product['price'], 0); ?>
                                        </td>
                                        
                                        <td class="text-center">
                                            <div class="qty-group">
                                                <a href="cart.php?action=update_qty&id=<?php echo $p_id; ?>&mode=decrease" class="btn-qty">
                                                    <i class="fas fa-minus small"></i>
                                                </a>
                                                <input type="text" class="qty-display" value="<?php echo $qty; ?>" readonly>
                                                <a href="cart.php?action=update_qty&id=<?php echo $p_id; ?>&mode=increase" class="btn-qty">
                                                    <i class="fas fa-plus small"></i>
                                                </a>
                                            </div>
                                        </td>
                                        
                                        <td class="text-end font-tech text-primary fw-bold fs-5">
                                            ฿<?php echo number_format($subtotal, 0); ?>
                                        </td>
                                        <td class="text-end">
                                            <a href="cart.php?action=remove&id=<?php echo $p_id; ?>" 
                                               class="btn-remove" 
                                               onclick="return confirm('Remove this item from inventory?');">
                                                <i class="fas fa-times-circle fs-5"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endif; endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <a href="index.php" class="text-decoration-none text-muted small font-tech hover-glow">
                            <i class="fas fa-long-arrow-alt-left me-2"></i> CONTINUE SHOPPING
                        </a>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="summary-card shadow-lg">
                        <div class="summary-header">
                            <h5 class="mb-0 font-tech text-white"><i class="fas fa-file-invoice-dollar me-2"></i> ORDER SUMMARY</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between mb-3 text-secondary">
                                <span>Total Items</span>
                                <span class="font-tech text-white"><?php echo count($_SESSION['cart']); ?> Items</span>
                            </div>
                            <div class="d-flex justify-content-between mb-4">
                                <span class="h5 mb-0 text-white">GRAND TOTAL</span>
                                <span class="h3 mb-0 text-primary font-tech" style="text-shadow: 0 0 15px rgba(59, 130, 246, 0.5);">
                                    ฿<?php echo number_format($total_order, 0); ?>
                                </span>
                            </div>

                            <hr class="border-secondary opacity-25 mb-4">

                           <?php if(isset($_SESSION['user_id'])): ?>
                                <a href="order_confirm.php" class="pay-btn text-white">
                                    <span class="btn-text">CONFIRM PURCHASE</span>
                                    <div class="icon-container">
                                        <svg class="icon default-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <svg class="icon check-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </a>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-warning w-100 py-3 rounded-pill fw-bold font-tech">
                                    <i class="fas fa-lock me-2"></i> LOGIN TO BUY
                                </a>
                            <?php endif; ?>
                            <div class="text-center mt-3">
                                <small class="text-muted" style="font-size: 0.7rem;">
                                    <i class="fas fa-shield-alt me-1"></i> SECURE TRANSACTION ENCRYPTED
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>