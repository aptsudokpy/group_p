<?php 
require_once '../config/db.php'; 

// เช็ค Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login_admin.php");
    exit();
}

// --- Logic เพิ่มประเภทสินค้า ---
if (isset($_POST['add_category'])) {
    $name = $_POST['name'];
    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        if ($stmt->execute([$name])) {
            $msg = "✅ เพิ่มประเภท '$name' เรียบร้อย";
        }
    }
}

// --- Logic ลบประเภทสินค้า ---
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    // ลบประเภท (ควรระวัง: ถ้าสินค้ายึดติดกับ id นี้อยู่อาจจะมีปัญหา แต่เบื้องต้นลบได้เลย)
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    echo "<script>window.location='manage_categories.php';</script>";
}

// ดึงข้อมูลประเภททั้งหมด
$stmt = $conn->query("SELECT * FROM categories ORDER BY id ASC");
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการประเภทสินค้า - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="d-flex">
        <div class="bg-dark text-white p-3 vh-100 sticky-top" style="width: 250px;">
            <h4 class="text-center">Admin Panel</h4>
            <hr>
            <ul class="nav flex-column gap-2">
                <li><a href="index.php" class="nav-link text-white-50">📊 ภาพรวม</a></li>
                <li><a href="manage_products.php" class="nav-link text-white-50">📦 จัดการสินค้า</a></li>
                <li><a href="manage_categories.php" class="nav-link active bg-primary text-white rounded">📂 จัดการประเภท</a></li>
                <li><a href="manage_orders.php" class="nav-link text-white-50">🚚 จัดการออเดอร์</a></li>
                <li><a href="manage_users.php" class="nav-link text-white-50">👥 จัดการลูกค้า</a></li>
                <li class="mt-4"><a href="logout.php" class="nav-link text-danger">ออกจากระบบ</a></li>
            </ul>
        </div>

        <div class="container-fluid p-4">
            <h2 class="mb-4">📂 จัดการประเภทสินค้า (Categories)</h2>
            
            <?php if(isset($msg)) echo "<div class='alert alert-success'>$msg</div>"; ?>

            <div class="row">
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white fw-bold">เพิ่มหมวดหมู่ใหม่</div>
                        <div class="card-body">
                            <form action="manage_categories.php" method="POST">
                                <div class="mb-3">
                                    <label class="form-label">ชื่อหมวดหมู่</label>
                                    <input type="text" name="name" class="form-control" placeholder="เช่น ปืนพก, ปืนยาว, ระเบิด" required>
                                </div>
                                <button type="submit" name="add_category" class="btn btn-success w-100">+ เพิ่มหมวดหมู่</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>ชื่อหมวดหมู่</th>
                                        <th>จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($categories as $c): ?>
                                    <tr>
                                        <td><?php echo $c['id']; ?></td>
                                        <td><?php echo $c['name']; ?></td>
                                        <td>
                                            <a href="manage_categories.php?delete_id=<?php echo $c['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('ยืนยันที่จะลบหมวดหมู่นี้?');">ลบ</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>