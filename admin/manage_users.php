<?php
// 1. เริ่ม Session เพื่อเช็คสิทธิ์ Admin
session_start();

// เปิดแสดง Error สำหรับตรวจสอบ
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/db.php';

// ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
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
    $role = $check->fetchColumn();
    
    // ตรวจสอบว่าไม่ใช่ admin
    if ($role !== 'admin') {
        try {
            // 1. ลบรายละเอียดออเดอร์ (order_details)
            $stmt_details = $conn->prepare("DELETE FROM order_details WHERE order_id IN (SELECT id FROM orders WHERE user_id = ?)");
            $stmt_details->execute([$id]);
            
            // 2. ลบออเดอร์
            $stmt_orders = $conn->prepare("DELETE FROM orders WHERE user_id = ?");
            $stmt_orders->execute([$id]);
            
            // 3. ลบผู้ใช้
            $stmt_user = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt_user->execute([$id]);
            
            echo "<script>alert('✅ ลบข้อมูลลูกค้าและประวัติการสั่งซื้อเรียบร้อย'); window.location='manage_users.php';</script>";
        } catch (PDOException $e) {
            echo "<script>alert('❌ เกิดข้อผิดพลาด: " . htmlspecialchars($e->getMessage()) . "'); window.location='manage_users.php';</script>";
        }
    } else {
        echo "<script>alert('❌ ไม่สามารถลบแอดมินได้'); window.location='manage_users.php';</script>";
    }
}

// --- Logic 3: แก้ไขข้อมูลลูกค้า ---
if (isset($_POST['update_user'])) {
    $uid      = $_POST['user_id'];
    $fullname = $_POST['fullname'];
    $phone    = $_POST['phone'];
    $address  = $_POST['address'];

    $stmt = $conn->prepare("UPDATE users SET fullname = ?, phone = ?, address = ? WHERE id = ?");
    if ($stmt->execute([$fullname, $phone, $address, $uid])) {
        echo "<script>alert('✅ อัปเดตข้อมูลลูกค้าเรียบร้อย'); window.location='manage_users.php';</script>";
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
    <style>
        :root {
            --bg-dark:      #0f121a;
            --card-bg:      rgba(36, 40, 50, 0.9);
            --accent-blue:  #5353ff;
            --neon-blue:    #00d2ff;
            --text-gray:    #7e8590;
            --border-color: #42434a;
        }

        body {
            background-color: var(--bg-dark);
            color: #ffffff;
            font-family: 'Segoe UI', Tahoma, sans-serif;
        }

        /* ── Sidebar ── */
        .sidebar {
            background: linear-gradient(180deg, rgba(36,40,50,1) 0%, rgba(20,22,28,1) 100%);
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

        .nav-link:hover,
        .nav-link.active {
            background-color: var(--accent-blue);
            color: #fff !important;
            box-shadow: 0 0 15px rgba(83, 83, 255, 0.3);
        }

        /* ── Layout ── */
        .main-container { padding: 40px; width: 100%; }

        /* ── Card & Table ── */
        .content-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.5);
            overflow: hidden;
        }

        .table                  { color: #fff; margin-bottom: 0; }
        .table thead            { background: rgba(255,255,255,0.05); }
        .table th               { color: var(--neon-blue); border-bottom-color: var(--border-color); padding: 15px; font-weight: 500; }
        .table td               { border-bottom-color: rgba(255,255,255,0.05); vertical-align: middle; padding: 15px; }

        .btn-action {
            width: 32px; height: 32px;
            display: inline-flex; align-items: center; justify-content: center;
            border-radius: 6px; transition: 0.2s;
        }

        /* ── Modal Dark Theme ── */
        .modal-content {
            background-color: #1a1d26;
            color: white;
            border: 1px solid var(--border-color);
            border-radius: 15px;
        }
        .modal-header          { border-bottom: 1px solid var(--border-color); }
        .modal-footer          { border-top:    1px solid var(--border-color); }

        /* ── Modal Body (profile card) ── */
        .profile-modal-body    { background: #fff; border-radius: 0 0 14px 14px; }
    </style>
</head>
<body>

<div class="d-flex">

    <!-- ════════════════ SIDEBAR ════════════════ -->
    <div class="sidebar p-4 vh-100 sticky-top">
        <h3 class="text-center fw-bold mb-4" style="color: var(--neon-blue);">
            <i class="fas fa-user-shield me-2"></i>ADMIN
        </h3>
        <hr class="border-secondary mb-4">
        <ul class="nav flex-column">
            <li><a href="index.php"             class="nav-link"><i class="fas fa-chart-line    me-2"></i> ภาพรวม</a></li>
            <li><a href="manage_products.php"   class="nav-link"><i class="fas fa-box-open      me-2"></i> จัดการสินค้า</a></li>
            <li><a href="manage_categories.php" class="nav-link"><i class="fas fa-folder        me-2"></i> จัดการประเภท</a></li>
            <li><a href="manage_orders.php"     class="nav-link"><i class="fas fa-shopping-cart me-2"></i> จัดการออเดอร์</a></li>
            <li><a href="manage_users.php"      class="nav-link active"><i class="fas fa-users  me-2"></i> จัดการลูกค้า</a></li>
            <li class="mt-4">
                <a href="../user_interface/index.php" class="nav-link" style="color: var(--neon-blue) !important;" target="_blank">
                    <i class="fas fa-external-link-alt me-2"></i> ไปหน้าร้านค้า
                </a>
            </li>
            <li class="mt-2">
                <a href="logout.php" class="nav-link text-danger">
                    <i class="fas fa-sign-out-alt me-2"></i> ออกจากระบบ
                </a>
            </li>
        </ul>
    </div>

    <!-- ════════════════ MAIN CONTENT ════════════════ -->
    <div class="main-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold m-0">
                👥 จัดการลูกค้า
                <span style="color: var(--neon-blue); font-size: 1rem;">& รายงานพฤติกรรม</span>
            </h2>
        </div>

        <div class="content-card">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ลูกค้า</th>
                            <th>วันที่สมัคร</th>
                            <th class="text-center">สถานะรายงาน</th>
                            <th class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td class="text-muted"><?php echo $u['id']; ?></td>
                            <td>
                                <div class="fw-bold"><?php echo htmlspecialchars($u['fullname']); ?></div>
                                <small style="color: var(--neon-blue);">@<?php echo htmlspecialchars($u['username']); ?></small>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($u['created_at'])); ?></td>
                            <td class="text-center">
                                <?php if (!empty($u['admin_message'])): ?>
                                    <span class="badge bg-danger rounded-pill">⚠️ มีประวัติ/คำเตือน</span>
                                <?php else: ?>
                                    <span class="badge bg-success rounded-pill">ปกติ</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <!-- ดูข้อมูลส่วนตัว -->
                                    <button class="btn btn-sm btn-primary btn-action"
                                            data-bs-toggle="modal"
                                            data-bs-target="#profileModal<?php echo $u['id']; ?>"
                                            title="ดูข้อมูลส่วนตัว">
                                        <i class="fas fa-user-circle"></i>
                                    </button>
                                    <!-- ประวัติการซื้อ -->
                                    <button class="btn btn-sm btn-info text-white btn-action"
                                            data-bs-toggle="modal"
                                            data-bs-target="#orderModal<?php echo $u['id']; ?>"
                                            title="ดูประวัติการซื้อ">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                    <!-- แก้ไขข้อมูล -->
                                    <button class="btn btn-sm btn-success btn-action"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editModal<?php echo $u['id']; ?>"
                                            title="แก้ไขข้อมูล">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <!-- รายงานพฤติกรรม -->
                                    <button class="btn btn-sm btn-warning btn-action"
                                            data-bs-toggle="modal"
                                            data-bs-target="#reportModal<?php echo $u['id']; ?>"
                                            title="เขียนรายงาน">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </button>
                                    <!-- ลบ -->
                                    <a href="manage_users.php?delete_id=<?php echo $u['id']; ?>"
                                       class="btn btn-sm btn-outline-danger btn-action"
                                       onclick="return confirm('ยืนยันลบลูกค้านี้? ข้อมูลการสั่งซื้อจะหายไปด้วย');"
                                       title="ลบลูกค้า">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div><!-- /main-container -->
</div><!-- /d-flex -->


<!-- ════════════════════════════════════════════
     MODALS (วนลูปสร้างทุก Modal ต่อ user 1 ชุด)
════════════════════════════════════════════ -->
<?php foreach ($users as $u): ?>

    <!-- ── 1. Profile Modal ── -->
    <div class="modal fade" id="profileModal<?php echo $u['id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-id-card me-2"></i>ข้อมูลลูกค้า</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body profile-modal-body">
                    <div class="text-center mb-4">
                        <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center"
                             style="width:70px; height:70px;">
                            <i class="fas fa-user fa-2x text-white"></i>
                        </div>
                        <h5 class="mt-2 text-dark fw-bold"><?php echo htmlspecialchars($u['fullname']); ?></h5>
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item text-dark">
                            Username: <span class="float-end fw-bold"><?php echo htmlspecialchars($u['username']); ?></span>
                        </li>
                        <li class="list-group-item text-dark">
                            เบอร์โทร: <span class="float-end fw-bold"><?php echo !empty($u['phone']) ? htmlspecialchars($u['phone']) : '-'; ?></span>
                        </li>
                        <li class="list-group-item text-dark border-0">
                            ที่อยู่:<br>
                            <span class="text-muted small">
                                <?php echo !empty($u['address']) ? nl2br(htmlspecialchars($u['address'])) : 'ไม่ระบุที่อยู่'; ?>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- ── 2. Order History Modal ── -->
    <div class="modal fade" id="orderModal<?php echo $u['id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-shopping-cart me-2"></i>ประวัติการสั่งซื้อ: <?php echo htmlspecialchars($u['fullname']); ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <?php
                        $stmt_o = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
                        $stmt_o->execute([$u['id']]);
                        $user_orders = $stmt_o->fetchAll();
                    ?>
                    <?php if (count($user_orders) > 0): ?>
                        <table class="table mb-0">
                            <thead class="small text-uppercase">
                                <tr>
                                    <th class="ps-4">#Order</th>
                                    <th>ยอดเงิน</th>
                                    <th>สถานะ</th>
                                    <th class="pe-4">วันที่</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($user_orders as $order): ?>
                                <tr>
                                    <td class="ps-4">#<?php echo $order['id']; ?></td>
                                    <td>฿<?php echo number_format($order['total_price'], 2); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo strtoupper($order['status']); ?></span></td>
                                    <td class="pe-4"><?php echo date('d/m/Y', strtotime($order['order_date'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-center text-muted py-4">ไม่มีประวัติการสั่งซื้อ</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ── 3. Edit Customer Modal ── -->
    <div class="modal fade" id="editModal<?php echo $u['id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="manage_users.php" method="POST">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title"><i class="fas fa-pencil-alt me-2"></i>✏️ แก้ไขข้อมูลลูกค้า</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">

                        <div class="mb-3">
                            <label class="form-label text-white-50">ชื่อลูกค้า</label>
                            <input type="text" name="fullname" class="form-control bg-dark text-white border-secondary"
                                   value="<?php echo htmlspecialchars($u['fullname']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white-50">เบอร์โทร</label>
                            <input type="text" name="phone" class="form-control bg-dark text-white border-secondary"
                                   value="<?php echo htmlspecialchars($u['phone'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white-50">ที่อยู่</label>
                            <textarea name="address" class="form-control bg-dark text-white border-secondary"
                                      rows="3"><?php echo htmlspecialchars($u['address'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" name="update_user" class="btn btn-success">
                            <i class="fas fa-save me-1"></i>บันทึกการแก้ไข
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ── 4. Report Modal ── -->
    <div class="modal fade" id="reportModal<?php echo $u['id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="manage_users.php" method="POST">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title text-dark"><i class="fas fa-exclamation-triangle me-2"></i>📝 รายงานพฤติกรรม</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-white-50">
                                ถึงคุณ: <?php echo htmlspecialchars($u['fullname']); ?>
                            </label>
                            <textarea name="admin_message"
                                      class="form-control bg-dark text-white border-secondary"
                                      rows="5"
                                      placeholder="ระบุข้อความตักเตือน..."><?php echo htmlspecialchars($u['admin_message'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" name="save_report" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>บันทึก
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php endforeach; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>