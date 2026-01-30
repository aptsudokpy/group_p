<?php 
require_once '../config/db.php'; 

// ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../user_interface/login.php");
    exit();
}

// --- 1. กำหนดสถานะที่ต้องการ (ตัดออกให้เหลือเท่าที่ใช้) ---
$status_options = [
    'pending' => '⏳ รอตรวจสอบการชำระเงิน',
    'paid' => '💰 ชำระเงินสำเร็จ',
    'delivered' => '✅ จัดส่งสำเร็จ'
];

// --- 2. Logic อัปเดตสถานะ (Dropdown) ---
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    if ($stmt->execute([$status, $order_id])) {
        $msg = "✅ อัปเดตสถานะออเดอร์ #$order_id เป็น '" . $status_options[$status] . "' แล้ว";
    }
}

// --- 3. Logic ลบคำสั่งซื้อ (ยกเลิกโดย Admin) ---
if (isset($_POST['cancel_order'])) {
    $order_id = $_POST['order_id'];
    
    // เปลี่ยนสถานะเป็น cancelled เพื่อให้ฝั่ง User รู้ว่าถูกยกเลิก
    $stmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
    if ($stmt->execute([$order_id])) {
        $msg = "⚠️ ยกเลิก (ลบ) คำสั่งซื้อ #$order_id เรียบร้อยแล้ว (แจ้งเตือนไปยังลูกค้าแล้ว)";
    }
}

// ดึงข้อมูลออเดอร์ทั้งหมด
$sql = "SELECT orders.*, users.fullname, users.username, users.address 
        FROM orders 
        JOIN users ON orders.user_id = users.id 
        ORDER BY orders.order_date DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการออเดอร์ - Admin</title>
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
                <li><a href="manage_products.php" class="nav-link text-white-50">📦 จัดการสินค้า </a></li>
                <li><a href="manage_categories.php" class="nav-link text-white-50">📂 จัดการประเภท</a></li>
                <li><a href="manage_orders.php" class="nav-link active bg-primary text-white rounded">🚚 จัดการออเดอร์</a></li>
                <li><a href="manage_users.php" class="nav-link text-white-50">👥 จัดการลูกค้า</a></li>
                <li class="mt-4"><a href="logout.php" class="nav-link text-danger">ออกจากระบบ</a></li>
            </ul>
        </div>

        <div class="container-fluid p-4">
            <h2 class="mb-4">🚚 จัดการสถานะการจัดส่ง</h2>

            <?php if(isset($msg)): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle"></i> <?php echo $msg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#ID</th>
                                <th width="20%">ข้อมูลลูกค้า</th>
                                <th width="10%">ยอดรวม</th>
                                <th width="15%">สถานะ</th>
                                <th width="40%">จัดการ / เปลี่ยนสถานะ</th>
                                <th width="10%">เพิ่มเติม</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($orders as $order): ?>
                            <tr>
                                <td><strong>#<?php echo $order['id']; ?></strong></td>
                                
                                <td>
                                    <div class="fw-bold"><?php echo $order['fullname']; ?></div>
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt"></i> <?php echo mb_strimwidth($order['address'], 0, 40, '...'); ?>
                                    </small>
                                </td>

                                <td class="fw-bold text-success">฿<?php echo number_format($order['total_price'], 2); ?></td>

                                <td>
                                    <?php 
                                        $s = $order['status'];
                                        $status_badge = '';
                                        
                                        switch ($s) {
                                            case 'pending':
                                                $status_badge = '<span class="badge bg-warning text-dark">⏳ รอตรวจสอบ</span>';
                                                break;
                                            case 'paid':
                                                $status_badge = '<span class="badge bg-info text-dark">💰 ชำระเงินสำเร็จ</span>';
                                                break;
                                            case 'delivered':
                                                $status_badge = '<span class="badge bg-success">✅ จัดส่งสำเร็จ</span>';
                                                break;
                                            case 'cancelled':
                                                $status_badge = '<span class="badge bg-danger">❌ ยกเลิกคำสั่งซื้อ</span>';
                                                break;
                                            default:
                                                // กรณีเจอสถานะแปลกๆ ที่ไม่มีในระบบ
                                                $status_badge = '<span class="badge bg-secondary">'. $s .'</span>';
                                        }
                                        echo $status_badge;
                                    ?>
                                </td>

                                <td>
                                    <?php if($order['status'] !== 'cancelled'): ?>
                                        <div class="d-flex gap-2">
                                            <form action="manage_orders.php" method="POST" class="d-flex gap-2 flex-grow-1">
                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                
                                                <select name="status" class="form-select form-select-sm border-primary">
                                                    <?php foreach($status_options as $key => $label): ?>
                                                        <option value="<?php echo $key; ?>" <?php if($order['status'] == $key) echo 'selected'; ?>>
                                                            <?php echo $label; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>

                                                <button type="submit" name="update_status" class="btn btn-sm btn-primary text-nowrap">
                                                    <i class="fas fa-save"></i> บันทึก
                                                </button>
                                            </form>

                                            <form action="manage_orders.php" method="POST" onsubmit="return confirm('⚠️ ยืนยันการลบ?');">
                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                <button type="submit" name="cancel_order" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">⛔ ออเดอร์นี้ถูกยกเลิกแล้ว</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-dark w-100">
                                        🔍 ดูสินค้า
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>