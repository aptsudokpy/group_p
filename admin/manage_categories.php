<?php 
require_once '../config/db.php'; 

// --- Logic เดิม ห้ามเปลี่ยน ---
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login_admin.php");
    exit();
}

if (isset($_POST['add_category'])) {
    $name = $_POST['name'];
    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        if ($stmt->execute([$name])) {
            $msg = "✅ เพิ่มประเภท '$name' เรียบร้อย";
        }
    }
}

if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    echo "<script>window.location='manage_categories.php';</script>";
}

$stmt = $conn->query("SELECT * FROM categories ORDER BY id ASC");
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการประเภทสินค้า - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-dark: #0f121a;
            --card-bg: rgba(36, 40, 50, 0.9);
            --accent-blue: #5353ff;
            --neon-blue: #00d2ff;
            --text-gray: #7e8590;
            --border-color: #42434a;
        }

        body {
            background-color: var(--bg-dark);
            color: #ffffff;
            font-family: 'Segoe UI', sans-serif;
            overflow-x: hidden;
        }

        /* Sidebar - UI เดียวกับ Manage Product */
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

        /* Layout & Cards - UI เดียวกับ Manage Product */
        .main-container { padding: 40px; width: 100%; }
        
        .content-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            padding: 25px;
        }

        /* Table - UI เดียวกับ Manage Product */
        .table { color: #fff; border-color: var(--border-color); }
        .table-light { background: rgba(255,255,255,0.05) !important; color: var(--neon-blue) !important; border: none; }
        .table-hover tbody tr:hover { background: rgba(255,255,255,0.03); transform: scale(1.002); transition: 0.2s; }

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
                <li><a href="manage_products.php" class="nav-link"><i class="fas fa-box-open me-2"></i> จัดการสินค้า</a></li>
                <li><a href="manage_categories.php" class="nav-link active"><i class="fas fa-folder me-2"></i> จัดการประเภท</a></li>
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
            <h2 class="fw-bold mb-4" style="letter-spacing: 2px;">CATEGORY <span style="color: var(--neon-blue);">MANAGEMENT</span></h2>
            
            <?php if(isset($msg)) echo "<div class='alert alert-success bg-success text-white border-0 shadow mb-4'>$msg</div>"; ?>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="content-card shadow-sm">
                        <h5 class="fw-bold mb-3" style="color: var(--neon-blue);">เพิ่มหมวดหมู่ใหม่</h5>
                        <form action="manage_categories.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label text-gray small">ชื่อหมวดหมู่</label>
                               <input type="text" name="name" class="form-control" placeholder="เช่น ปืนพก, ปืนยาว, ระเบิด" style="color: #cccccc;" required>
                            </div>
                            <button type="submit" name="add_category" class="btn btn-neon-blue w-100 py-2">
                                <i class="fas fa-plus me-2"></i>เพิ่มหมวดหมู่
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="content-card shadow-sm">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="100">ID</th>
                                        <th>ชื่อหมวดหมู่</th>
                                        <th width="150" class="text-center">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($categories as $c): ?>
                                    <tr>
                                        <td class="fw-bold" style="color: var(--neon-blue);">#<?php echo $c['id']; ?></td>
                                        <td><?php echo $c['name']; ?></td>
                                        <td class="text-center">
                                            <a href="manage_categories.php?delete_id=<?php echo $c['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger px-3" 
                                               onclick="return confirm('ยืนยันที่จะลบหมวดหมู่นี้?');">
                                                <i class="fas fa-trash-alt me-1"></i> ลบ
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div> </div>
    </div>
</body>
</html>