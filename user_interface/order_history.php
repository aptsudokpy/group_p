<?php 
require_once '../config/db.php'; 

// เช็คว่าล็อกอินไหม
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ดึงข้อมูลคำสั่งซื้อทั้งหมดของ user นี้ เรียงจากใหม่ไปเก่า
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ประวัติการสั่งซื้อ - Group P</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include '../includes/navbar.php'; ?>

    <div class="container mt-5">
        <h3 class="mb-4 fw-bold">📜 ประวัติการสั่งซื้อของคุณ</h3>

        <?php if (count($orders) == 0): ?>
            <div class="alert alert-warning text-center p-5">
                <h4>คุณยังไม่มีประวัติการสั่งซื้อ</h4>
                <a href="index.php" class="btn btn-primary mt-3">ไปเลือกซื้อสินค้า</a>
            </div>
        <?php else: ?>
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="15%">เลขที่คำสั่งซื้อ</th>
                                <th width="20%">วันที่สั่งซื้อ</th>
                                <th width="15%">ยอดรวม</th>
                                <th width="35%">สถานะ</th>
                                <th width="15%">รายละเอียด</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><strong>#<?php echo $order['id']; ?></strong></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                                <td class="fw-bold text-success">฿<?php echo number_format($order['total_price'], 2); ?></td>
                                
                                <td>
                                    <?php 
                                    $status_color = 'secondary';
                                    $status_text = 'ไม่ทราบสถานะ';
                                    
                                    // Map สถานะให้ตรงกับฝั่ง Admin
                                    switch($order['status']) {
                                        case 'pending': 
                                            $status_color = 'warning text-dark'; 
                                            $status_text = '⏳ รอตรวจสอบการชำระเงิน'; 
                                            break;
                                        
                                        case 'paid': 
                                            $status_color = 'info text-dark'; 
                                            $status_text = '💰 ชำระเงินสำเร็จ / กำลังเตรียมส่ง'; 
                                            break;

                                        case 'delivered': 
                                            $status_color = 'success'; 
                                            $status_text = '✅ จัดส่งสำเร็จ'; 
                                            break;

                                        case 'cancelled': 
                                            $status_color = 'danger'; 
                                            // ข้อความตามที่ต้องการ
                                            $status_text = '❌ ถูกยกเลิกโดยผู้ดูแล'; 
                                            break;
                                        
                                        default:
                                            $status_text = $order['status'];
                                    }
                                    ?>
                                    <span class="badge bg-<?php echo $status_color; ?> p-2">
                                        <?php echo $status_text; ?>
                                    </span>
                                </td>

                                <td>
                                    <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary w-100">
                                        ดูรายการ
                                    </a> 
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>