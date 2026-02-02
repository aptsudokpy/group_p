<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../config/db.php'; 

// ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../user_interface/login.php");
    exit();
}

// --- Logic 1: บันทึกรายงาน (Report) ---
if (isset($_POST['save_report'])) {
    $uid = $_POST['user_id'];
    $msg = $_POST['admin_message'];
    
    $stmt = $conn->prepare("UPDATE users SET admin_message = ? WHERE id = ?");
    if ($stmt->execute([$msg, $uid])) {
        echo "<script>alert('✅ บันทึกรายงานเรียบร้อย'); window.location='manage_users.php';</script>";
    }
}

// --- Logic 2: ลบลูกค้า ---
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $check = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $check->execute([$id]);
    if ($check->fetchColumn() !== 'admin') {
        $conn->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
        echo "<script>alert('ลบผู้ใช้งานเรียบร้อย'); window.location='manage_users.php';</script>";
    }
}

// ดึงข้อมูลลูกค้า (User) ทั้งหมด
$stmt = $conn->prepare("SELECT * FROM users WHERE role = 'user' ORDER BY id DESC");
$stmt->execute();
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการลูกค้า - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="d-flex">
        <div class="bg-dark text-white p-3 vh-100 sticky-top" style="width: 250px;">
            <h4 class="text-center">Admin Panel</h4>
            <hr>
            <ul class="nav flex-column gap-2">
                <li><a href="index.php" class="nav-link text-white-50">📊 ภาพรวม</a></li>
                <li><a href="manage_products.php" class="nav-link text-white-50">📦 จัดการสินค้า</a></li>
                <li><a href="manage_categories.php" class="nav-link text-white-50">📂 จัดการประเภท</a></li>
                <li><a href="manage_orders.php" class="nav-link text-white-50">🚚 จัดการออเดอร์</a></li>
                <li><a href="manage_users.php" class="nav-link active bg-primary text-white rounded">👥 จัดการลูกค้า</a></li>
                <li class="mt-4"><a href="logout.php" class="nav-link text-danger">ออกจากระบบ</a></li>
            </ul>
        </div>

        <div class="container-fluid p-4">
            <h2 class="mb-4">👥 จัดการลูกค้า & รายงานพฤติกรรม</h2>
            
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>ลูกค้า</th>
                                <th>วันที่สมัคร</th>
                                <th>สถานะรายงาน</th>
                                <th width="35%">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $u): ?>
                            <tr>
                                <td><?php echo $u['id']; ?></td>
                                <td>
                                    <div class="fw-bold"><?php echo $u['fullname']; ?></div>
                                    <small class="text-muted">@<?php echo $u['username']; ?></small>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($u['created_at'])); ?></td>
                                <td>
                                    <?php if(!empty($u['admin_message'])): ?>
                                        <span class="badge bg-danger">⚠️ มีประวัติ/คำเตือน</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">ปกติ</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary me-1" data-bs-toggle="modal" data-bs-target="#profileModal<?php echo $u['id']; ?>" title="ดูข้อมูลส่วนตัว">
                                        <i class="fas fa-user-circle"></i>
                                    </button>

                                    <button class="btn btn-sm btn-info text-white me-1" data-bs-toggle="modal" data-bs-target="#orderModal<?php echo $u['id']; ?>" title="ดูประวัติการซื้อ">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>

                                    <button class="btn btn-sm btn-warning me-1" data-bs-toggle="modal" data-bs-target="#reportModal<?php echo $u['id']; ?>" title="เขียนรายงาน">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </button>

                                    <a href="manage_users.php?delete_id=<?php echo $u['id']; ?>" 
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('ยืนยันลบลูกค้านี้? ข้อมูลการสั่งซื้อจะหายไปด้วย');">
                                       <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div> 
    </div>

    <?php foreach($users as $u): ?>
        
        <div class="modal fade" id="profileModal<?php echo $u['id']; ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fas fa-id-card"></i> ข้อมูลลูกค้า</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="fas fa-user fa-3x text-secondary"></i>
                            </div>
                            <h5 class="mt-2"><?php echo $u['fullname']; ?></h5>
                            <span class="badge bg-secondary">User ID: <?php echo $u['id']; ?></span>
                        </div>
                        
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <strong><i class="fas fa-user-tag"></i> Username:</strong> 
                                <span class="float-end"><?php echo $u['username']; ?></span>
                            </li>
                            <li class="list-group-item">
                                <strong><i class="fas fa-envelope"></i> Email:</strong> 
                                <span class="float-end"><?php echo !empty($u['email']) ? $u['email'] : '-'; ?></span>
                            </li>
                            <li class="list-group-item">
                                <strong><i class="fas fa-phone"></i> เบอร์โทร:</strong> 
                                <span class="float-end"><?php echo !empty($u['phone']) ? $u['phone'] : '-'; ?></span>
                            </li>
                            <li class="list-group-item">
                                <strong><i class="fas fa-map-marker-alt"></i> ที่อยู่:</strong> 
                                <p class="text-muted mb-0 mt-1 small"><?php echo !empty($u['address']) ? $u['address'] : 'ไม่ระบุที่อยู่'; ?></p>
                            </li>
                            <li class="list-group-item">
                                <strong><i class="fas fa-clock"></i> วันที่สมัคร:</strong> 
                                <span class="float-end"><?php echo date('d/m/Y H:i', strtotime($u['created_at'])); ?></span>
                            </li>
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="orderModal<?php echo $u['id']; ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">ประวัติการสั่งซื้อ: <?php echo $u['fullname']; ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <?php 
                            $stmt_o = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
                            $stmt_o->execute([$u['id']]);
                            $user_orders = $stmt_o->fetchAll();
                        ?>
                        <?php if(count($user_orders) > 0): ?>
                            <table class="table table-bordered table-sm">
                                <thead class="table-light">
                                    <tr><th>#Order</th><th>ยอดเงิน</th><th>สถานะ</th><th>วันที่</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach($user_orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td>฿<?php echo number_format($order['total_price'], 2); ?></td>
                                        <td>
                                            <?php 
                                                $status_color = match($order['status']) {
                                                    'pending' => 'bg-warning',
                                                    'preparing' => 'bg-info',
                                                    'shipped' => 'bg-primary',
                                                    'completed' => 'bg-success',
                                                    'cancelled' => 'bg-danger',
                                                    default => 'bg-secondary'
                                                };
                                            ?>
                                            <span class="badge <?php echo $status_color; ?>"><?php echo strtoupper($order['status']); ?></span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($order['order_date'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="text-center text-muted py-3">ยังไม่เคยสั่งซื้อสินค้า</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="reportModal<?php echo $u['id']; ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="manage_users.php" method="POST">
                        <div class="modal-header bg-warning">
                            <h5 class="modal-title">📝 รายงานพฤติกรรม</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                            <div class="mb-3">
                                <label class="form-label fw-bold">ถึงคุณ: <?php echo $u['fullname']; ?></label>
                                <textarea name="admin_message" class="form-control" rows="5" placeholder="ระบุข้อความตักเตือน..."><?php echo $u['admin_message']; ?></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                            <button type="submit" name="save_report" class="btn btn-primary">บันทึก</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    <?php endforeach; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>