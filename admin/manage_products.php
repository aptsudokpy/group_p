<?php 
require_once '../config/db.php'; 

// --- ห้ามยุ่ง Logic เดิมตามสั่ง ---
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../user_interface/login.php");
    exit();
}

if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    if ($product && $product['image']) {
        if (file_exists("../assets/images/" . $product['image'])) unlink("../assets/images/" . $product['image']);
    }
    $conn->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    echo "<script>window.location='manage_products.php';</script>";
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$cat_filter = isset($_GET['cat_id']) ? $_GET['cat_id'] : '';

$sql = "SELECT products.*, categories.name AS category_name 
        FROM products 
        LEFT JOIN categories ON products.category_id = categories.id 
        WHERE 1=1";
$params = [];

if (!empty($cat_filter)) {
    $sql .= " AND products.category_id = ?";
    $params[] = $cat_filter;
}

if (!empty($search)) {
    $sql .= " AND products.name LIKE ?";
    $params[] = "%$search%";
}

$sql .= " ORDER BY products.id DESC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
$cats = $conn->query("SELECT * FROM categories")->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการสินค้า - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-dark: #0f121a;
            --card-bg: rgba(36, 40, 50, 0.9);
            --accent-blue: #5353ff;
            --neon-blue: #00d2ff;
            --neon-pink: #ff5353;
            --text-gray: #7e8590;
            --border-color: #42434a;
        }

        body {
            background-color: var(--bg-dark);
            color: #ffffff;
            font-family: 'Segoe UI', sans-serif;
            overflow-x: hidden;
        }

        /* Sidebar สไตล์เดิมที่คุยกัน */
        .sidebar {
            background: linear-gradient(180deg, rgba(36, 40, 50, 1) 0%, rgba(20, 22, 28, 1) 100%);
            border-right: 1px solid var(--border-color);
            min-width: 260px;
        }

        .nav-link {
            color: var(--text-gray) !important;
            transition: all 0.3s;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 5px;
        }

        .nav-link:hover, .nav-link.active {
            background-color: var(--accent-blue);
            color: #fff !important;
            box-shadow: 0 0 15px rgba(83, 83, 255, 0.4);
        }

        /* Elements Styling */
        .main-container { padding: 40px; width: 100%; }
        
        .content-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            padding: 25px;
        }

        .table { color: #fff; border-color: var(--border-color); }
        .table-light { background: rgba(255,255,255,0.05) !important; color: var(--neon-blue) !important; border: none; }
        .table-hover tbody tr:hover { background: rgba(255,255,255,0.03); transform: scale(1.005); transition: 0.2s; }

        .form-control {
            background: rgba(0,0,0,0.2);
            border: 1px solid var(--border-color);
            color: #fff;
        }
        .form-control:focus {
            background: rgba(0,0,0,0.3);
            border-color: var(--neon-blue);
            color: #fff;
            box-shadow: 0 0 10px rgba(0,210,255,0.2);
        }

        .btn-neon-blue { background: var(--accent-blue); color: white; border: none; }
        .btn-neon-blue:hover { background: #4040ff; color: white; box-shadow: 0 0 15px var(--accent-blue); }
        
        .btn-outline-neon { color: var(--neon-blue); border: 1px solid var(--neon-blue); }
        .btn-outline-neon:hover, .btn-outline-neon.active { background: var(--neon-blue); color: #000; box-shadow: 0 0 15px var(--neon-blue); }

        .product-img {
            width: 60px; height: 60px; object-fit: cover;
            border-radius: 10px; border: 2px solid var(--border-color);
        }

        .badge-cat { background: rgba(83, 83, 255, 0.2); color: var(--neon-blue); border: 1px solid var(--neon-blue); }
        
    </style>
</head>
<body>

    <div class="d-flex">
        <div class="sidebar p-4 vh-100 sticky-top">
            <h3 class="text-center fw-bold mb-4" style="color: var(--neon-blue); text-shadow: 0 0 10px rgba(0,210,255,0.3);">
                <i class="fas fa-user-shield me-2"></i>ADMIN
            </h3>
            <hr class="border-secondary mb-4">
            <ul class="nav flex-column">
                <li><a href="index.php" class="nav-link"><i class="fas fa-chart-line me-2"></i> ภาพรวม</a></li>
                <li><a href="manage_products.php" class="nav-link active"><i class="fas fa-box-open me-2"></i> จัดการสินค้า</a></li>
                <li><a href="manage_categories.php" class="nav-link"><i class="fas fa-folder me-2"></i> จัดการประเภท</a></li>
                <li><a href="manage_orders.php" class="nav-link"><i class="fas fa-shopping-cart me-2"></i> จัดการออเดอร์</a></li>
                <li><a href="manage_users.php" class="nav-link"><i class="fas fa-users me-2"></i> จัดการลูกค้า</a></li>
                <li class="mt-4">
                    <a href="../user_interface/index.php" class="nav-link" style="color: var(--neon-blue) !important;" target="_blank">
                        <i class="fas fa-external-link-alt me-2"></i> ไปหน้าร้านค้า
                    </a>
                </li>
                <li class="mt-2"><a href="logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt me-2"></i> ออกจากระบบ</a></li>
            </ul>
        </div>

        <div class="main-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold" style="letter-spacing: 2px;">PRODUCT <span style="color: var(--neon-blue);">MANAGEMENT</span></h2>
                <a href="product_form.php" class="btn btn-success btn-lg px-4 shadow-sm" style="border-radius: 10px;">
                    <i class="fas fa-plus-circle me-2"></i>เพิ่มสินค้าใหม่
                </a>
            </div>

            <div class="mb-4">
                <div class="btn-group gap-2">
                    <a href="manage_products.php" class="btn btn-outline-neon rounded <?php echo ($cat_filter == '') ? 'active' : ''; ?>">
                        ทั้งหมด
                    </a>
                    <?php foreach($cats as $c): ?>
                        <a href="manage_products.php?cat_id=<?php echo $c['id']; ?>" 
                           class="btn btn-outline-neon rounded <?php echo ($cat_filter == $c['id']) ? 'active' : ''; ?>">
                           <?php echo $c['name']; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="content-card mb-4 py-3">
                <form action="manage_products.php" method="GET" class="row g-3 align-items-center">
                    <?php if($cat_filter): ?>
                        <input type="hidden" name="cat_id" value="<?php echo $cat_filter; ?>">
                    <?php endif; ?>
                    <div class="col-auto"><label class="fw-bold text-gray"><i class="fas fa-search"></i> ค้นหา:</label></div>
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control" placeholder="ชื่ออาวุธที่ต้องการหา..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-neon-blue px-4">ค้นหา</button>
                        <?php if($search || $cat_filter): ?>
                            <a href="manage_products.php" class="btn btn-outline-secondary">ล้างค่า</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="content-card">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="100">รูปภาพ</th>
                                <th>ชื่ออาวุธ / สินค้า</th>
                                <th>หมวดหมู่</th>
                                <th>ราคา</th>
                                <th width="180" class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($products) > 0): ?>
                                <?php foreach($products as $p): ?>
                                <tr>
                                    <td>
                                        <?php if(!empty($p['image'])): ?>
                                            <img src="../assets/images/<?php echo $p['image']; ?>" class="product-img">
                                        <?php else: ?>
                                            <div class="product-img d-flex align-items-center justify-content-center bg-dark text-muted">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold"><?php echo $p['name']; ?></td>
                                    <td>
                                        <?php if($p['category_name']): ?>
                                            <span class="badge badge-cat px-3 py-2"><?php echo $p['category_name']; ?></span>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="color: var(--neon-blue); font-size: 1.1rem;" class="fw-bold">
                                        ฿<?php echo number_format($p['price'], 2); ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a href="product_form.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-warning mx-1 rounded">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="manage_products.php?delete_id=<?php echo $p['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger mx-1 rounded" 
                                               onclick="return confirm('ยืนยันการทำลายข้อมูลสินค้านี้?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fas fa-ghost fa-3x mb-3"></i><br>
                                        ไม่พบข้อมูลสินค้าที่ค้นหา
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>