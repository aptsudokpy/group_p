<?php 
require_once '../config/db.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../user_interface/login.php");
    exit();
}

// --- Logic ลบสินค้า ---
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

// --- เตรียมตัวแปรค้นหาและกรอง ---
$search = isset($_GET['search']) ? $_GET['search'] : '';
$cat_filter = isset($_GET['cat_id']) ? $_GET['cat_id'] : ''; // รับค่า cat_id จาก URL

// สร้าง SQL Query แบบ Dynamic
$sql = "SELECT products.*, categories.name AS category_name 
        FROM products 
        LEFT JOIN categories ON products.category_id = categories.id 
        WHERE 1=1"; // 1=1 เพื่อให้เติม AND ได้ง่ายๆ
$params = [];

// ถ้ามีการเลือกหมวดหมู่
if (!empty($cat_filter)) {
    $sql .= " AND products.category_id = ?";
    $params[] = $cat_filter;
}

// ถ้ามีการค้นหา
if (!empty($search)) {
    $sql .= " AND products.name LIKE ?";
    $params[] = "%$search%";
}

$sql .= " ORDER BY products.id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// ดึงหมวดหมู่ทั้งหมดมาทำปุ่ม Filter
$cats = $conn->query("SELECT * FROM categories")->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการสินค้า - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="d-flex">
        <div class="bg-dark text-white p-3 vh-100 sticky-top" style="width: 250px;">
            <h4 class="text-center">Admin Panel</h4>
            <hr>
            <ul class="nav flex-column gap-2">
                <li><a href="index.php" class="nav-link text-white-50">📊 ภาพรวม</a></li>
                <li><a href="manage_products.php" class="nav-link active bg-primary text-white rounded">📦 จัดการสินค้า</a></li>
                <li><a href="manage_categories.php" class="nav-link text-white-50">📂 จัดการประเภท</a></li>
                <li><a href="manage_orders.php" class="nav-link text-white-50">🚚 จัดการออเดอร์</a></li>
                <li><a href="manage_users.php" class="nav-link text-white-50">👥 จัดการลูกค้า</a></li>
                <li class="mt-4"><a href="logout.php" class="nav-link text-danger">ออกจากระบบ</a></li>
            </ul>
        </div>

        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>📦 จัดการสินค้า (อาวุธ)</h2>
                <a href="product_form.php" class="btn btn-success">+ เพิ่มสินค้าใหม่</a>
            </div>

            <div class="mb-4">
                <div class="btn-group shadow-sm" role="group">
                    <a href="manage_products.php" class="btn btn-outline-dark <?php echo ($cat_filter == '') ? 'active' : ''; ?>">
                        ทั้งหมด
                    </a>
                    <?php foreach($cats as $c): ?>
                        <a href="manage_products.php?cat_id=<?php echo $c['id']; ?>" 
                           class="btn btn-outline-dark <?php echo ($cat_filter == $c['id']) ? 'active' : ''; ?>">
                           <?php echo $c['name']; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body py-3">
                    <form action="manage_products.php" method="GET" class="row g-2 align-items-center">
                        <?php if($cat_filter): ?>
                            <input type="hidden" name="cat_id" value="<?php echo $cat_filter; ?>">
                        <?php endif; ?>

                        <div class="col-auto"><label class="fw-bold">ค้นหา:</label></div>
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="พิมพ์ชื่อสินค้า..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">🔍 ค้นหา</button>
                            <?php if($search || $cat_filter): ?>
                                <a href="manage_products.php" class="btn btn-secondary">❌ รีเซ็ต</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="80">รูป</th>
                                <th>ชื่อสินค้า</th>
                                <th>หมวดหมู่</th> <th>ราคา</th>
                                <th width="150">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($products) > 0): ?>
                                <?php foreach($products as $p): ?>
                                <tr>
                                    <td>
                                        <?php if(!empty($p['image'])): ?>
                                            <img src="../assets/images/<?php echo $p['image']; ?>" class="rounded border" style="width: 50px; height: 50px; object-fit: cover;">
                                        <?php else: ?>
                                            <span class="text-muted small">ไม่มีรูป</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $p['name']; ?></td>
                                    <td>
                                        <?php if($p['category_name']): ?>
                                            <span class="badge bg-secondary"><?php echo $p['category_name']; ?></span>
                                        <?php else: ?>
                                            <span class="text-muted small">- ไม่ระบุ -</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-success fw-bold">฿<?php echo number_format($p['price'], 2); ?></td>
                                    <td>
                                        <a href="product_form.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-warning">แก้ไข</a>
                                        <a href="manage_products.php?delete_id=<?php echo $p['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('ยืนยันลบ?');">ลบ</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">🚫 ไม่พบสินค้าในหมวดหมู่นี้ หรือ คำค้นหานี้</td>
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