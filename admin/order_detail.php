<?php 
require_once '../config/db.php'; 

if (!isset($_GET['id'])) {
    header("Location: manage_orders.php");
    exit();
}

$order_id = $_GET['id'];

// 1. ดึงข้อมูลหัวบิล (Order Info) + ข้อมูลลูกค้า
$sql = "SELECT orders.*, users.fullname, users.address, users.username 
        FROM orders 
        JOIN users ON orders.user_id = users.id 
        WHERE orders.id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$order_id]);
$order = $stmt->fetch();

// 2. ดึงรายการสินค้าในบิลนี้ (Order Items)
$sql_items = "SELECT order_details.*, products.name, products.image 
              FROM order_details 
              JOIN products ON order_details.product_id = products.id 
              WHERE order_details.order_id = ?";
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
</head>
<body class="bg-light">

    <div class="container mt-5 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>📦 รายละเอียดคำสั่งซื้อ #<?php echo $order_id; ?></h3>
            <a href="manage_orders.php" class="btn btn-secondary">← กลับหน้ารายการ</a>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-white fw-bold">ข้อมูลลูกค้า</div>
                    <div class="card-body">
                        <p class="mb-1"><strong>ชื่อ:</strong> <?php echo $order['fullname']; ?> (@<?php echo $order['username']; ?>)</p>
                        <p class="mb-1"><strong>วันที่สั่ง:</strong> <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></p>
                        <p class="mb-1"><strong>สถานะ:</strong> <span class="badge bg-primary"><?php echo strtoupper($order['status']); ?></span></p>
                        <hr>
                        <h6 class="fw-bold">ที่อยู่จัดส่ง:</h6>
                        <p class="text-muted"><?php echo nl2br($order['address']); ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold">สินค้าที่ต้องจัดส่ง</div>
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>สินค้า</th>
                                    <th>ราคาต่อชิ้น</th>
                                    <th>จำนวน</th>
                                    <th>รวม</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="../assets/images/<?php echo !empty($item['image']) ? $item['image'] : 'no-image.jpg'; ?>" 
                                                 width="40" height="40" class="me-2 rounded border object-fit-cover">
                                            <?php echo $item['name']; ?>
                                        </div>
                                    </td>
                                    <td>฿<?php echo number_format($item['price'], 2); ?></td>
                                    <td>x <?php echo $item['quantity']; ?></td>
                                    <td class="fw-bold">฿<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">ยอดสุทธิ</td>
                                    <td class="fw-bold text-success fs-5">฿<?php echo number_format($order['total_price'], 2); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                
                <div class="mt-3 text-end">
                    <button onclick="window.print()" class="btn btn-outline-dark">🖨️ พิมพ์ใบปะหน้า</button>
                </div>
            </div>
        </div>
    </div>

</body>
</html>