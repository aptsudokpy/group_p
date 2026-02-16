<?php 
require_once '../config/db.php'; 

// ตรวจสอบว่ามี ID ส่งมาไหม
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

// --- แก้ไข SQL: Join ตาราง categories เพื่อดึงชื่อหมวดหมู่มาด้วย ---
$sql = "SELECT p.*, c.name AS category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ?";
        
$stmt = $conn->prepare($sql);
$stmt->execute([$id]);
$product = $stmt->fetch();

// ถ้าหาไม่เจอให้กลับหน้าแรก
if (!$product) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> - Weapon Shop</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- THEME CONFIG --- */
        :root {
            --primary-glow: #3b82f6;
            --accent-color: #00d2ff;
            --dark-bg: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
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

        /* --- DETAIL PAGE STYLES --- */
        .detail-card {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
        }

        .img-container {
            position: relative;
            background: radial-gradient(circle at center, #334155 0%, #020617 100%);
            min-height: 500px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }

        .product-img {
            max-width: 90%;
            max-height: 450px;
            filter: drop-shadow(0 10px 20px rgba(0,0,0,0.6));
            /* เอา Animation: transform scale/rotate ออกแล้วครับ */
        }

        /* Style ป้ายหมวดหมู่แบบใหม่ */
        .category-badge {
            font-family: 'Kanit', sans-serif; /* ใช้ Kanit เพื่อรองรับภาษาไทยสวยๆ */
            font-weight: 600;
            background: rgba(59, 130, 246, 0.15); /* พื้นหลังฟ้าจางๆ */
            border: 1px solid var(--primary-glow);
            color: var(--accent-color);
            padding: 8px 20px;
            border-radius: 4px; /* มุมเหลี่ยมเล็กน้อยสไตล์ Tech */
            text-transform: uppercase;
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.2);
            backdrop-filter: blur(5px);
            letter-spacing: 1px;
        }

        .price-badge {
            font-family: 'Orbitron', sans-serif;
            font-size: 2.5rem;
            color: #10b981;
            text-shadow: 0 0 20px rgba(16, 185, 129, 0.4);
            font-weight: bold;
        }

        .qty-input {
            background-color: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.2);
            color: white !important;
            text-align: center;
            font-family: 'Orbitron', sans-serif;
        }
        .qty-input:focus {
            background-color: rgba(255,255,255,0.1);
            border-color: var(--primary-glow);
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.3);
        }

        .btn-add-cart {
            background: linear-gradient(45deg, #2563eb, #00d2ff);
            border: none;
            font-family: 'Orbitron', sans-serif;
            font-weight: bold;
            letter-spacing: 1px;
            color: white;
            transition: all 0.3s;
        }
        
        .btn-add-cart:hover {
            background: linear-gradient(45deg, #1d4ed8, #2563eb);
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.6);
            transform: translateY(-2px);
            color: white;
        }

        .btn-back {
            color: rgba(255,255,255,0.6);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 50px;
            padding: 8px 20px;
            transition: 0.3s;
        }
        .btn-back:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            border-color: white;
        }

        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            margin: 2rem 0;
        }
    </style>
</head>
<body>

    <?php include '../includes/navbar.php'; ?>

    <div class="container mt-5 mb-5">
        
        <div class="mb-4">
            <a href="index.php" class="btn-back text-decoration-none font-tech small">
                <i class="fas fa-chevron-left me-1"></i> BACK TO ARMORY
            </a>
        </div>

        <div class="detail-card">
            <div class="row g-0">
                <div class="col-lg-6 img-container">
                    <?php if(!empty($product['image'])): ?>
                        <img src="../assets/images/<?php echo $product['image']; ?>" 
                             class="img-fluid product-img" 
                             alt="<?php echo $product['name']; ?>">
                    <?php else: ?>
                        <div class="text-center text-muted opacity-50">
                            <i class="fas fa-cube fa-5x mb-3"></i>
                            <p class="font-tech">NO PREVIEW AVAILABLE</p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="position-absolute top-0 start-0 m-4">
                        <span class="category-badge">
                            <?php echo !empty($product['category_name']) ? htmlspecialchars($product['category_name']) : 'UNKNOWN TYPE'; ?>
                        </span>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card-body p-5 h-100 d-flex flex-column justify-content-center">
                        
                        <h5 class="text-primary font-tech mb-2">MODEL ID: #<?php echo str_pad($product['id'], 4, '0', STR_PAD_LEFT); ?></h5>
                        <h1 class="display-4 fw-bold text-white mb-3 text-uppercase" style="text-shadow: 0 0 10px rgba(255,255,255,0.1);">
                            <?php echo $product['name']; ?>
                        </h1>
                        
                        <div class="price-badge mb-4">
                            ฿<?php echo number_format($product['price'], 0); ?>
                        </div>

                        <div class="divider"></div>

                        <p class="text-secondary mb-4" style="line-height: 1.8; font-size: 1.1rem;">
                            <?php echo nl2br($product['description']); ?>
                        </p>

                        <div class="row mb-5 gx-3 opacity-75">
                            <div class="col-4">
                                <small class="text-muted font-tech">DAMAGE</small>
                                <div class="progress" style="height: 6px; background: rgba(255,255,255,0.1);">
                                    <div class="progress-bar bg-danger" style="width: 85%"></div>
                                </div>
                            </div>
                            <div class="col-4">
                                <small class="text-muted font-tech">ACCURACY</small>
                                <div class="progress" style="height: 6px; background: rgba(255,255,255,0.1);">
                                    <div class="progress-bar bg-info" style="width: 70%"></div>
                                </div>
                            </div>
                            <div class="col-4">
                                <small class="text-muted font-tech">FIRE RATE</small>
                                <div class="progress" style="height: 6px; background: rgba(255,255,255,0.1);">
                                    <div class="progress-bar bg-warning" style="width: 60%"></div>
                                </div>
                            </div>
                        </div>

                        <form action="cart.php" method="POST" class="mt-auto">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <input type="hidden" name="action" value="add">
                            
                            <div class="row g-3 align-items-end">
                                <div class="col-4 col-sm-3">
                                    <label class="form-label text-muted font-tech small">QUANTITY</label>
                                    <input type="number" name="quantity" class="form-control form-control-lg qty-input" value="1" min="1">
                                </div>
                                <div class="col-8 col-sm-9">
                                    <button type="submit" class="btn btn-add-cart btn-lg w-100 py-3 rounded-pill">
                                        <i class="fas fa-cart-plus me-2"></i> ADD TO INVENTORY
                                    </button>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
        
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>