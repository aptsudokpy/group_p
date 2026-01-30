<?php 
require_once '../config/db.php'; 

// 1. เช็ค Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2. เช็คว่ามี ID ส่งมาไหม
if (!isset($_GET['id'])) {
    header("Location: order_history.php");
    exit();
}

$order_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// 3. ดึงข้อมูล Order หลัก (ต้องเป็นของ User คนนี้เท่านั้น เพื่อความปลอดภัย)
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    echo "<script>alert('ไม่พบคำสั่งซื้อ หรือคุณไม่มีสิทธิ์เข้าถึง'); window.location='order_history.php';</script>";
    exit();
}

// 4. ดึงรายการสินค้าใน Order นี้ (Join กับตาราง products เพื่อเอารูปและชื่อ)
$sql_items = "SELECT oi.*, p.name, p.image 
              FROM order_items oi 
              JOIN products p ON oi.product_id = p.id 
              WHERE oi.order_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายละเอียดคำสั่งซื้อ #<?php echo $order_id; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

    <?php include '../includes/navbar.php'; ?>

    <div class="container mt-5 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="order_history.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> ย้อนกลับ
            </a>
            <h4 class="fw-bold m-0">📦 รายละเอียดคำสั่งซื้อ #<?php echo $order_id; ?></h4>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 border-end">
                        <small class="text-muted">วันที่สั่งซื้อ</small>
                        <p class="fw-bold"><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></p>
                    </div>
                    <div class="col-md-4 border-end">
                        <small class="text-muted">สถานะ</small>
                        <br>
                        <?php 
                            $s = $order['status'];
                            switch ($s) {
                                case 'pending':
                                    echo '<span class="badge bg-warning text-dark">⏳ รอตรวจสอบ</span>'; break;
                                case 'paid':
                                    echo '<span class="badge bg-info text-dark">💰 ชำระเงินสำเร็จ</span>'; break;
                                case 'delivered':
                                    echo '<span class="badge bg-success">✅ จัดส่งสำเร็จ</span>'; break;
                                case 'cancelled':
                                    echo '<span class="badge bg-danger">❌ ยกเลิกคำสั่งซื้อ</span>'; break;
                                default:
                                    echo '<span class="badge bg-secondary">'.$s.'</span>';
                            }
                        ?>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">ราคารวมทั้งสิ้น</small>
                        <h4 class="fw-bold text-primary">฿<?php echo number_format($order['total_price'], 2); ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="m-0 fw-bold">รายการสินค้า</h5>
            </div>
            <div class="card-body p-0">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50%;">สินค้า</th>
                            <th class="text-center">ราคาต่อชิ้น</th>
                            <th class="text-center">จำนวน</th>
                            <th class="text-end">รวม</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="../assets/images/<?php echo $item['image'] ? $item['image'] : 'no-image.jpg'; ?>" 
                                         alt="Product" 
                                         class="rounded object-fit-cover me-3" 
                                         style="width: 60px; height: 60px;">
                                    <div>
                                        <div class="fw-bold"><?php echo $item['name']; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">฿<?php echo number_format($item['price'], 2); ?></td>
                            <td class="text-center">x <?php echo $item['quantity']; ?></td>
                            <td class="text-end fw-bold">฿<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end fw-bold">ยอดรวมสุทธิ</td>
                            <td class="text-end fw-bold text-primary fs-5">฿<?php echo number_format($order['total_price'], 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>