<?php 
session_start();
require_once '../config/db.php'; 

// --- 1. เตรียมตัวแปรสำหรับค้นหาและกรอง ---
$search = isset($_GET['search']) ? $_GET['search'] : '';
$cat_id = isset($_GET['cat_id']) ? $_GET['cat_id'] : '';

// --- 2. สร้าง SQL Query แบบ Dynamic ---
$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

// ถ้ามีการเลือกหมวดหมู่ (ที่ไม่ใช่ค่าว่าง)
if (!empty($cat_id)) {
    $sql .= " AND category_id = ?";
    $params[] = $cat_id;
}

// ถ้ามีการค้นหาชื่อสินค้า
if (!empty($search)) {
    $sql .= " AND name LIKE ?";
    $params[] = "%$search%";
}

$sql .= " ORDER BY id DESC"; // สินค้าใหม่สุดขึ้นก่อน

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// ดึงหมวดหมู่ทั้งหมดมาใส่ใน Dropdown
$cats = $conn->query("SELECT * FROM categories")->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าร้านค้า - Group P</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .product-card {
            transition: transform 0.2s;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body class="bg-light">

    <?php include '../includes/navbar.php'; ?>

    <header class="bg-dark text-white text-center py-5 mb-4" style="background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://uiverse.io/build/images/placeholder-landscape.jpg'); background-size: cover; background-position: center;">
        <div class="container">
            <h1 class="display-4 fw-bold">Weapon Shop</h1>
            <p class="lead">คลังแสงคุณภาพ อาวุธครบมือ สำหรับมืออาชีพ</p>
            
            <div class="row justify-content-center mt-4">
                <div class="col-md-8">
                    <form action="index.php" method="GET">
                        <div class="input-group input-group-lg shadow-sm">
                            
                            <select name="cat_id" class="form-select" style="max-width: 200px; background-color: #f8f9fa;">
                                <option value="">ประเภทสินค้า</option>
                                <?php foreach($cats as $c): ?>
                                    <option value="<?php echo $c['id']; ?>" 
                                        <?php if($cat_id == $c['id']) echo 'selected'; ?>>
                                        <?php echo $c['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <input type="text" name="search" class="form-control border-start-0" 
                                   placeholder="ค้นหาชื่อสินค้า... (เช่น M4, Glock)" 
                                   value="<?php echo htmlspecialchars($search); ?>">
                            
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-search"></i> ค้นหา
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </header>

    <div class="container mb-5">
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <?php if($search || $cat_id): ?>
                    <h5 class="text-muted">
                        ผลการค้นหา: 
                        <?php if($cat_id): ?>
                            <span class="badge bg-secondary">หมวดหมู่ที่เลือก</span>
                        <?php endif; ?>
                        <?php if($search): ?>
                            "<strong><?php echo htmlspecialchars($search); ?></strong>"
                        <?php endif; ?>
                    </h5>
                    <a href="index.php" class="btn btn-sm btn-outline-danger">❌ ล้างค่าค้นหา</a>
                <?php else: ?>
                    <h4 class="fw-bold">สินค้าทั้งหมด</h4>
                <?php endif; ?>
            </div>
            <div class="text-muted small">พบ <?php echo count($products); ?> รายการ</div>
        </div>

        <div class="row g-4">
            <?php if(count($products) > 0): ?>
                <?php foreach($products as $p): ?>
                <div class="col-md-3 col-sm-6">
                    <div class="card h-100 border-0 shadow-sm product-card">
                        <div style="height: 200px; overflow: hidden;" class="bg-white d-flex align-items-center justify-content-center">
                            <?php if(!empty($p['image'])): ?>
                                <img src="../assets/images/<?php echo $p['image']; ?>" class="card-img-top" style="max-height: 100%; width: auto;" alt="<?php echo $p['name']; ?>">
                            <?php else: ?>
                                <span class="text-muted">ไม่มีรูปภาพ</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-truncate"><?php echo $p['name']; ?></h5>
                            <p class="card-text text-muted small flex-grow-1">
                                <?php echo mb_strimwidth($p['description'], 0, 50, '...'); ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="fs-5 fw-bold text-success">฿<?php echo number_format($p['price'], 2); ?></span>
                                <a href="product_detail.php?id=<?php echo $p['id']; ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye"></i> ดูรายละเอียด
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <h3 class="text-muted"><i class="fas fa-box-open fa-2x mb-3"></i></h3>
                    <h4>ไม่พบสินค้าที่คุณค้นหา</h4>
                    <a href="index.php" class="btn btn-secondary mt-3">ดูสินค้าทั้งหมด</a>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <footer class="bg-dark text-white text-center py-3 mt-auto">
        <p class="mb-0">© 2024 Weapon Shop System. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>