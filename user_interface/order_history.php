<?php 
require_once '../config/db.php'; 

// เช็คว่าล็อกอินไหม
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- แก้ไข SQL เพื่อดึงรูปสินค้าชิ้นแรกของแต่ละออเดอร์มาโชว์ด้วย ---
$sql = "SELECT o.*, 
        (SELECT p.image FROM order_details od 
         JOIN products p ON od.product_id = p.id 
         WHERE od.order_id = o.id LIMIT 1) as product_preview,
        (SELECT COUNT(*) FROM order_details WHERE order_id = o.id) as item_count
        FROM orders o 
        WHERE o.user_id = ? 
        ORDER BY o.order_date DESC";

$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการสั่งซื้อ - Weapon Shop</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-glow: #3b82f6;
            --dark-bg: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.6);
        }

        body {
            background-color: var(--dark-bg);
            color: #f8fafc;
            font-family: 'Kanit', sans-serif;
            min-height: 100vh;
        }

        .font-tech { font-family: 'Orbitron', sans-serif; letter-spacing: 1px; }

        .log-container {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
        }

        .table {
            --bs-table-bg: transparent;
            --bs-table-color: #f8fafc;
            margin-bottom: 0;
        }

        thead { background: rgba(0, 0, 0, 0.3); }
        thead th {
            font-family: 'Kanit', sans-serif;
            color: var(--primary-glow) !important;
            padding: 20px !important;
            border-bottom: 2px solid var(--primary-glow) !important;
            font-weight: 600;
        }

        /* --- PRODUCT THUMBNAIL --- */
        .order-img-preview {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.1);
            background: #1e293b;
        }

        .item-stack {
            position: relative;
            display: inline-block;
        }
        .item-count-badge {
            position: absolute;
            bottom: -5px;
            right: -5px;
            background: var(--primary-glow);
            color: white;
            font-size: 0.65rem;
            padding: 2px 6px;
            border-radius: 10px;
            font-family: 'Orbitron', sans-serif;
        }

        /* --- STATUS BADGES (THAI) --- */
        .status-pill {
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 400;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid transparent;
        }

        .status-pending { background: rgba(245, 158, 11, 0.1); color: #fbbf24; border-color: rgba(245, 158, 11, 0.3); }
        .status-paid { background: rgba(6, 182, 212, 0.1); color: #22d3ee; border-color: rgba(6, 182, 212, 0.3); }
        .status-delivered { background: rgba(16, 185, 129, 0.1); color: #34d399; border-color: rgba(16, 185, 129, 0.3); }
        .status-cancelled { background: rgba(239, 68, 68, 0.1); color: #f87171; border-color: rgba(239, 68, 68, 0.3); }

        .btn-detail {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            color: white;
            font-size: 0.85rem;
            padding: 8px 15px;
            border-radius: 6px;
            transition: 0.3s;
        }
        .btn-detail:hover {
            background: var(--primary-glow);
            color: white;
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.4);
        }
    </style>
</head>
<body>

    <?php include '../includes/navbar.php'; ?>

    <div class="container mt-5 mb-5">
        <div class="mb-4">
            <h6 class="text-primary font-tech mb-1">TERMINAL: <?php echo $_SESSION['username'] ?? 'USER'; ?></h6>
            <h2 class="fw-bold m-0"><i class="fas fa-history me-2 text-info"></i>ประวัติการสั่งซื้ออาวุธ</h2>
        </div>

        <?php if (count($orders) == 0): ?>
            <div class="log-container p-5 text-center">
                <i class="fas fa-folder-open fa-3x text-muted mb-3 opacity-25"></i>
                <h4 class="text-muted">ไม่พบประวัติการทำรายการ</h4>
                <a href="index.php" class="btn btn-outline-primary mt-3 rounded-pill px-4">ไปที่คลังแสง</a>
            </div>
        <?php else: ?>
            <div class="log-container">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th width="15%">เลขที่สั่งซื้อ</th>
                                <th width="10%">สินค้า</th>
                                <th width="20%">วันที่ทำรายการ</th>
                                <th width="15%">ยอดรวมสุทธิ</th>
                                <th width="25%">สถานะจัดส่ง</th>
                                <th width="15%" class="text-end">การจัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="font-tech text-white fw-bold">
                                    #<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?>
                                </td>
                                <td>
                                    <div class="item-stack">
                                        <img src="../assets/images/<?php echo $order['product_preview'] ?: 'no-image.jpg'; ?>" 
                                             class="order-img-preview">
                                        <?php if($order['item_count'] > 1): ?>
                                            <span class="item-count-badge">+<?php echo ($order['item_count'] - 1); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="small text-secondary">
                                    <?php echo date('d/m/Y', strtotime($order['order_date'])); ?><br>
                                    <span class="font-tech" style="font-size: 0.7rem;"><?php echo date('H:i', strtotime($order['order_date'])); ?> น.</span>
                                </td>
                                <td class="font-tech text-info fw-bold">
                                    ฿<?php echo number_format($order['total_price'], 0); ?>
                                </td>
                                
                                <td>
                                    <?php 
                                    $s_class = ''; $s_icon = ''; $s_text = '';
                                    
                                    switch($order['status']) {
                                        case 'pending': 
                                            $s_class = 'status-pending'; $s_icon = 'fa-clock'; $s_text = 'รอตรวจสอบชำระเงิน'; 
                                            break;
                                        case 'paid': 
                                            $s_class = 'status-paid'; $s_icon = 'fa-box'; $s_text = 'กำลังเตรียมอาวุธ'; 
                                            break;
                                        case 'delivered': 
                                            $s_class = 'status-delivered'; $s_icon = 'fa-shipping-fast'; $s_text = 'ส่งมอบสำเร็จ'; 
                                            break;
                                        case 'cancelled': 
                                            $s_class = 'status-cancelled'; $s_icon = 'fa-ban'; $s_text = 'รายการถูกยกเลิก'; 
                                            break;
                                    }
                                    ?>
                                    <div class="status-pill <?php echo $s_class; ?>">
                                        <i class="fas <?php echo $s_icon; ?>"></i>
                                        <?php echo $s_text; ?>
                                    </div>
                                </td>

                                <td class="text-end">
                                    <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-detail">
                                        รายละเอียด <i class="fas fa-chevron-right ms-1"></i>
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