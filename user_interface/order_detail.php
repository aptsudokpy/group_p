<?php 
require_once '../config/db.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: order_history.php");
    exit();
}

$order_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    echo "<script>alert('ไม่พบคำสั่งซื้อ'); window.location='order_history.php';</script>";
    exit();
}

$sql_items = "SELECT od.*, p.name, p.image 
              FROM order_details od 
              JOIN products p ON od.product_id = p.id 
              WHERE od.order_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo $order_id; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            /* สีพื้นหลังดึงจากรูปที่คุณส่งมา */
            --bg-dark-navy: #0a1120; 
            --card-navy: #111b2d;
            --accent-blue: #3b82f6;
        }

        body {
            background-color: var(--bg-dark-navy);
            color: #ffffff !important; /* บังคับตัวหนังสือขาวทั้งหน้า */
            font-family: 'Kanit', sans-serif;
            min-height: 100vh;
        }

        .font-tech { font-family: 'Orbitron', sans-serif; letter-spacing: 1px; }

        /* การ์ดข้อมูลส่วนบน */
        .info-card {
            background: var(--card-navy);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px;
        }

        /* ปรับสี Label ให้ขาวนวลมองเห็นชัด */
        .text-label {
            color: #e2e8f0 !important;
            font-size: 0.8rem;
            text-transform: uppercase;
        }

        /* ตารางรายการสินค้า */
        .details-container {
            background: var(--card-navy);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow: hidden;
        }

        .table {
            --bs-table-bg: transparent;
            --bs-table-color: #ffffff;
            margin-bottom: 0;
        }

        .table thead th {
            background: rgba(0, 0, 0, 0.2);
            color: var(--accent-blue) !important;
            border: none;
            padding: 15px;
        }

        .table tbody td {
            color: #ffffff !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding: 15px;
        }

        /* แถบรวมเงิน (TOTAL INVESTMENT) ปรับตามรูป */
        .total-row {
            background: #0f172a !important;
            border-top: 2px solid var(--accent-blue) !important;
        }

        /* กล่องข้อความลับด้านล่าง (สีขาวชัดเจน) */
        .footer-note-box {
            background: rgba(255, 255, 255, 0.03);
            border: 1px dashed rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            padding: 15px 20px;
            margin-top: 30px;
        }

        .footer-note-box p {
            color: #ffffff !important; /* ขาวบริสุทธิ์ */
            margin: 0;
            font-weight: 300;
        }

        .product-img {
            width: 60px; height: 60px;
            object-fit: cover; border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.1);
        }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="container mt-5 mb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="font-tech text-white m-0">ORDER DETAILS</h2>
        <a href="order_history.php" class="btn btn-outline-light btn-sm rounded-pill px-4">BACK</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="info-card">
                <div class="text-label font-tech">Order ID</div>
                <div class="h5 m-0 text-white">#<?php echo str_pad($order_id, 5, '0', STR_PAD_LEFT); ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-card">
                <div class="text-label font-tech">Order Date</div>
                <div class="h5 m-0 text-white"><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-card text-end">
                <div class="text-label font-tech">Status</div>
                <div class="h5 m-0">
                    <?php 
                        $s = $order['status'];
                        if($s == 'pending') echo '<span class="text-warning">รอตรวจสอบ</span>';
                        else if($s == 'paid') echo '<span class="text-info">ชำระเงินแล้ว</span>';
                        else if($s == 'delivered') echo '<span class="text-success">ส่งมอบสำเร็จ</span>';
                        else echo '<span class="text-danger">ยกเลิก</span>';
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div class="details-container shadow-lg">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr class="font-tech">
                        <th>ITEM</th>
                        <th class="text-center">PRICE</th>
                        <th class="text-center">QTY</th>
                        <th class="text-end">SUBTOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="../assets/images/<?php echo $item['image'] ?: 'no-image.jpg'; ?>" class="product-img me-3">
                                <span class="fw-bold"><?php echo $item['name']; ?></span>
                            </div>
                        </td>
                        <td class="text-center font-tech">฿<?php echo number_format($item['price'], 0); ?></td>
                        <td class="text-center">x <?php echo $item['quantity']; ?></td>
                        <td class="text-end font-tech text-info">฿<?php echo number_format($item['price'] * $item['quantity'], 0); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="2" class="p-4">
                            <span class="font-tech text-white h5">TOTAL INVESTMENT:</span>
                        </td>
                        <td colspan="2" class="text-end p-4">
                            <span class="font-tech text-info h3">฿<?php echo number_format($order['total_price'], 2); ?></span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="footer-note-box">
        <p>
            <i class="fas fa-user-secret me-2 text-info"></i>
            <strong>SECURITY NOTICE:</strong> ข้อมูลนี้ถือเป็นความลับระหว่างผู้ซื้อและคลังแสง <strong>Group P</strong> เท่านั้น กรุณาเก็บหลักฐานนี้ไว้จนกว่าจะได้รับสินค้า
        </p>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>