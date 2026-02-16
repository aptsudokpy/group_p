<?php 
session_start();

// --- เพิ่มส่วนนี้: เช็คว่า Login หรือยัง ---
if (!isset($_SESSION['user_id'])) {
    // ถ้ายังไม่มี session user_id (ยังไม่ล็อกอิน) ให้ดีดไปหน้า login ทันที
    header("Location: login.php");
    exit; // สำคัญมาก! ต้องสั่งปิดการทำงานต่อทันที
}
// ------------------------------------

require_once '../config/db.php'; 

// ... (โค้ดส่วนที่เหลือเหมือนเดิม) ...

// --- 1. เตรียมตัวแปรสำหรับค้นหาและกรอง ---
$search = isset($_GET['search']) ? $_GET['search'] : '';
$cat_id = isset($_GET['cat_id']) ? $_GET['cat_id'] : '';

// --- 2. สร้าง SQL Query แบบ Dynamic ---
$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

// ถ้ามีการเลือกหมวดหมู่
if (!empty($cat_id)) {
    $sql .= " AND category_id = ?";
    $params[] = $cat_id;
}

// ถ้ามีการค้นหาชื่อสินค้า
if (!empty($search)) {
    $sql .= " AND name LIKE ?";
    $params[] = "%$search%";
}

$sql .= " ORDER BY id DESC"; 

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// ดึงหมวดหมู่
$cats = $conn->query("SELECT * FROM categories")->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Weapon Shop</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- GLOBAL THEME --- */
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
            overflow-x: hidden;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* --- TYPOGRAPHY --- */
        h1, h2, h3, h4, h5 { font-weight: 600; }
        .font-tech { font-family: 'Orbitron', sans-serif; letter-spacing: 2px; }

        /* --- NAVBAR FIX --- */
        .navbar, nav {
            background-color: #020617 !important;
            background-image: none !important;
            backdrop-filter: none !important;
            box-shadow: 0 4px 10px rgba(0,0,0,0.5);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            z-index: 1000;
        }

        /* --- HERO SECTION --- */
        .hero-section {
            background: linear-gradient(to bottom, rgba(15, 23, 42, 0.85), rgba(15, 23, 42, 1)), 
                        url('https://images.unsplash.com/photo-1595591931583-3207b6619478?auto=format&fit=crop&q=80&w=2000');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            padding: 80px 0;
            position: relative;
            border-bottom: 1px solid rgba(59, 130, 246, 0.2);
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.5);
        }

        .title-glow {
            font-family: 'Orbitron', sans-serif;
            text-shadow: 0 0 20px rgba(59, 130, 246, 0.5);
            margin-bottom: 1rem;
        }

        .text-primary-gradient {
            background: linear-gradient(to right, #00d2ff, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 800;
        }

        /* --- MODERN SEARCH BAR --- */
        .modern-search-bar {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            padding: 5px;
        }

        .modern-search-bar:focus-within {
            border-color: var(--primary-glow);
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.25);
            background: rgba(255, 255, 255, 0.08);
        }

        .form-select { color: white !important; }
        .form-select option { background-color: #1e293b !important; color: #ffffff !important; padding: 10px; }
        .form-control::placeholder { color: rgba(255, 255, 255, 0.4); }

        /* --- PRODUCT CARDS --- */
        .product-card {
            background: var(--card-bg) !important;
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05) !important;
            border-radius: 16px !important;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            overflow: hidden;
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.4), 0 0 15px rgba(59, 130, 246, 0.2);
            border-color: rgba(59, 130, 246, 0.5) !important;
        }

        .card-img-wrapper {
            height: 220px; 
            overflow: hidden; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            background: radial-gradient(circle at center, #334155 0%, #0f172a 100%);
        }

        .card-img-wrapper img {
            transition: transform 0.5s ease;
            filter: drop-shadow(0 5px 15px rgba(0,0,0,0.5));
        }

        .product-card:hover .card-img-wrapper img {
            transform: scale(1.1) rotate(2deg);
        }

        .card-body { padding: 1.5rem; }
        .card-title { font-size: 1.1rem; font-weight: 600; color: #fff; }

        .price-tag {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.2rem;
            font-weight: bold;
            color: #10b981; 
            text-shadow: 0 0 10px rgba(16, 185, 129, 0.3);
        }

        /* >>>>>> BUTTON STYLES: BUBBLES ANIMATION <<<<<< */
        .bubbles {
            --c1: #ffffff;      /* สีตัวหนังสือตอน Hover (ขาว) */
            --c2: #3b82f6;      /* สีหลักของปุ่ม (ฟ้า) */
            --size-letter: 14px; /* ขนาดฟอนต์ (ปรับให้พอดีปุ่ม) */
            
            padding: 0.6em 1.4em;
            font-size: var(--size-letter);
            text-decoration: none !important;
            display: inline-block;
            
            background-color: transparent;
            border: 1px solid var(--c2);
            border-radius: 50px; /* ทรงแคปซูล */
            cursor: pointer;
            
            overflow: hidden;
            position: relative;
            transition: 300ms cubic-bezier(0.83, 0, 0.17, 1);
            z-index: 1;
        }

        .bubbles .text {
            font-weight: 700;
            color: var(--c2); /* สีตัวหนังสือปกติ (ฟ้า) */
            position: relative;
            z-index: 2;
            transition: color 700ms cubic-bezier(0.83, 0, 0.17, 1);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .bubbles::before,
        .bubbles::after {
            content: "";
            width: 150%;
            aspect-ratio: 1/1;
            scale: 0;
            transition: 1000ms cubic-bezier(0.76, 0, 0.24, 1);
            
            background-color: var(--c2); /* สีพื้นหลังตอน Bubble แตกตัว (ฟ้า) */
            border-radius: 50%;
            
            position: absolute;
            transform: translate(-50%, -50%); /* จัดกึ่งกลาง */
            z-index: 0;
        }

        .bubbles::before { top: 0; left: 0; }
        .bubbles::after { top: 100%; left: 100%; }

        /* Animation States */
        .bubbles:hover .text { color: var(--c1); }
        .bubbles:hover::before, .bubbles:hover::after { scale: 1; }
        .bubbles:active { transform: scale(0.95); filter: brightness(0.9); }
        /* >>>>>> END BUTTON STYLES <<<<<< */

        .btn-search-custom {
            background: var(--primary-glow);
            border: none;
            color: white;
        }
        .btn-search-custom:hover {
            background: #2563eb;
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.5);
        }

        footer {
            background: #020617 !important;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding: 2rem 0;
            margin-top: auto;
        }
        /* ล็อกหัวเว็บให้เลื่อนตาม */
        .navbar {
            position: fixed; /* หรือใช้ sticky-top */
            top: 0;
            width: 100%;
            z-index: 1030; /* ให้อยู่เหนือเลเยอร์อื่น */
            background-color: #111b2d !important; /* สีทึบตามสั่ง */
            border-bottom: 2px solid #3b82f6; /* เพิ่มเส้นใต้ให้ดูมีมิติ */
            box-shadow: 0 2px 10px rgba(0,0,0,0.5);
        }

        /* ป้องกันเนื้อหาโดนหัวเว็บทับ (ใส่ในหน้า index.php) */
        body {
            padding-top: 70px; /* เว้นระยะด้านบนให้เท่ากับความสูงของ Navbar */
        }
    </style>
</head>
<body>

    <?php include '../includes/navbar.php'; ?>

    <header class="hero-section text-white text-center mb-5">
        <div class="container position-relative z-2">
            
            <h1 class="display-3 fw-bold text-uppercase title-glow">
                AMMO <span class="text-primary-gradient">HERE</span>
            </h1>
            <p class="lead text-light opacity-75 mb-5 font-tech">
                ขายอาหารรายได้ไม่เด็ดขาย AK47 ดีกว่า
            </p>
            
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10">
                    <form action="index.php" method="GET">
                        <div class="modern-search-bar d-flex align-items-center rounded-pill shadow-lg">
                            
                            <div class="input-group flex-nowrap border-end border-secondary border-opacity-25 pe-2" style="max-width: 180px;">
                                <select name="cat_id" class="form-select bg-transparent border-0 fw-bold ps-3 font-tech" style="box-shadow: none; cursor: pointer;">
                                    <option value="" class="text-white">All Type</option>
                                    <?php foreach($cats as $c): ?>
                                        <option value="<?php echo $c['id']; ?>" 
                                            <?php if($cat_id == $c['id']) echo 'selected'; ?>>
                                            <?php echo $c['name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="flex-grow-1 px-2">
                                <input type="text" name="search" class="form-control bg-transparent border-0 text-white px-3" 
                                    style="box-shadow: none;"
                                    placeholder="Search for weapons..." 
                                    value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            
                            <div class="ps-2">
                                <button type="submit" class="btn btn-search-custom rounded-pill px-4 fw-bold d-flex align-items-center py-2">
                                    <i class="fas fa-search me-2"></i> Search
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>

        </div>
    </header>

    <div class="container mb-5">
        
        <div class="d-flex justify-content-between align-items-end mb-4 border-bottom border-secondary border-opacity-25 pb-3">
            <div>
                <?php if($search || $cat_id): ?>
                    <h5 class="text-muted mb-2">
                        <i class="fas fa-filter text-primary me-2"></i>ผลการค้นหา: 
                    </h5>
                    <div class="d-flex gap-2 align-items-center">
                        <?php if($cat_id): ?>
                            <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill">Category Selected</span>
                        <?php endif; ?>
                        <?php if($search): ?>
                            <span class="text-white h5 mb-0">"<?php echo htmlspecialchars($search); ?>"</span>
                        <?php endif; ?>
                        <a href="index.php" class="btn btn-sm btn-outline-danger ms-3 rounded-pill px-3">
                            <i class="fas fa-times me-1"></i> Reset
                        </a>
                    </div>
                <?php else: ?>
                    <h2 class="fw-bold text-white font-tech"><i class="fas fa-crosshairs me-2 text-primary"></i> สินค้าทั้งหมด </h2>
                <?php endif; ?>
            </div>
            <div class="text-muted small font-tech">
                TOTAL ITEMS: <span class="text-white fw-bold"><?php echo count($products); ?></span>
            </div>
        </div>

        <div class="row g-4">
            <?php if(count($products) > 0): ?>
                <?php foreach($products as $p): ?>
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                    <div class="card h-100 product-card">
                        <div class="card-img-wrapper">
                            <?php if(!empty($p['image'])): ?>
                                <img src="../assets/images/<?php echo $p['image']; ?>" class="card-img-top" style="max-height: 80%; width: auto;" alt="<?php echo $p['name']; ?>">
                            <?php else: ?>
                                <div class="text-center text-muted opacity-50">
                                    <i class="fas fa-cube fa-3x mb-2"></i>
                                    <p class="small mb-0">NO IMAGE</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-truncate mb-2"><?php echo $p['name']; ?></h5>
                            <p class="card-text text-muted small flex-grow-1" style="line-height: 1.6;">
                                <?php echo mb_strimwidth($p['description'], 0, 60, '...'); ?>
                            </p>
                            <hr class="border-secondary opacity-25 my-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="price-tag">฿<?php echo number_format($p['price'], 0); ?></span>
                                
                                <a href="product_detail.php?id=<?php echo $p['id']; ?>" class="bubbles">
                                    <span class="text">
                                        Details <i class="fas fa-arrow-right ms-1 small"></i>
                                    </span>
                                </a>
                                </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <div class="py-5" style="border: 2px dashed rgba(255,255,255,0.1); border-radius: 20px;">
                        <i class="fas fa-box-open fa-4x text-muted mb-3 opacity-50"></i>
                        <h4 class="text-white mt-3">ไม่พบสินค้าที่คุณค้นหา</h4>
                        <a href="index.php" class="btn btn-outline-primary mt-3 rounded-pill px-4">ดูสินค้าทั้งหมด</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <footer class="text-center">
        <div class="container">
            <p class="mb-0 text-muted small font-tech">© 2024 WEAPON SHOP SYSTEM. DESIGNED FOR PROFESSIONALS.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>