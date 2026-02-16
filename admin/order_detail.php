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
        }

        /* Sidebar สไตล์เดียวกับหน้าหลัก */
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
        }

        .main-container { padding: 40px; width: 100%; }

        .content-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.5);
            overflow: hidden;
        }

        .card-header-custom {
            background: rgba(255, 255, 255, 0.05);
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            font-weight: bold;
            color: var(--neon-blue);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .table { color: #fff; margin-bottom: 0; }
        .table thead { background: rgba(255, 255, 255, 0.03); }
        .table th { color: var(--text-gray); border-bottom-color: var(--border-color); font-weight: 500; }
        .table td { border-bottom-color: rgba(255, 255, 255, 0.05); vertical-align: middle; padding: 15px; }

        .status-badge {
            background: var(--accent-blue);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        .info-label { color: var(--text-gray); font-size: 0.9rem; }
        .info-value { color: #fff; font-weight: 500; }

        @media print {
            .sidebar, .btn-secondary, .btn-outline-dark, .mt-3.text-end { display: none !important; }
            body { background: #fff !important; color: #000 !important; }
            .content-card { border: 1px solid #ddd !important; box-shadow: none !important; background: #fff !important; }
            .card-header-custom { color: #000 !important; border-bottom: 1px solid #000 !important; }
            .table td, .table th { color: #000 !important; border-bottom: 1px solid #ddd !important; }
            .text-success { color: #000 !important; }
        }
    </style>
</head>
<body>

<div class="d-flex">
    <div class="sidebar p-4 vh-100 sticky-top">
        <h3 class="text-center fw-bold mb-4" style="color: var(--neon-blue);">
            <i class="fas fa-user-shield me-2"></i>ADMIN
        </h3>
        <hr class="border-secondary mb-4">
        <ul class="nav flex-column">
            <li><a href="index.php" class="nav-link"><i class="fas fa-chart-line me-2"></i> ภาพรวม</a></li>
            <li><a href="manage_products.php" class="nav-link"><i class="fas fa-box-open me-2"></i> จัดการสินค้า</a></li>
            <li><a href="manage_categories.php" class="nav-link"><i class="fas fa-folder me-2"></i> จัดการประเภท</a></li>
            <li><a href="manage_orders.php" class="nav-link active"><i class="fas fa-shopping-cart me-2"></i> จัดการออเดอร์</a></li>
            <li><a href="manage_users.php" class="nav-link"><i class="fas fa-users me-2"></i> จัดการลูกค้า</a></li>
            <li class="mt-4"><a href="logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt me-2"></i> ออกจากระบบ</a></li>
        </ul>
    </div>

    <div class="main-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold m-0">ORDER <span style="color: var(--neon-blue);">#<?php echo $order_id; ?></span></h2>
            <a href="manage_orders.php" class="btn btn-outline-light rounded-pill px-4">
                <i class="fas fa-arrow-left me-2"></i>กลับหน้ารายการ
            </a>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="content-card h-100">
                    <div class="card-header-custom">
                        <i class="fas fa-user-circle me-2"></i>ข้อมูลลูกค้า
                    </div>
                    <div class="p-4">
                        <div class="mb-3">
                            <div class="info-label">ชื่อผู้สั่งซื้อ</div>
                            <div class="info-value"><?php echo $order['fullname']; ?> (@<?php echo $order['username']; ?>)</div>
                        </div>
                        <div class="mb-3">
                            <div class="info-label">วันที่ทำรายการ</div>
                            <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></div>
                        </div>
                        <div class="mb-3">
                            <div class="info-label">สถานะปัจจุบัน</div>
                            <span class="status-badge"><?php echo strtoupper($order['status']); ?></span>
                        </div>
                        <hr class="border-secondary opacity-25">
                        <div>
                            <div class="info-label mb-2"><i class="fas fa-map-marker-alt me-1"></i> ที่อยู่จัดส่ง</div>
                            <div class="info-value text-gray fw-normal" style="line-height: 1.6;">
                                <?php echo nl2br($order['address']); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="content-card">
                    <div class="card-header-custom">
                        <i class="fas fa-box me-2"></i>สินค้าที่ต้องจัดส่ง
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>สินค้า</th>
                                    <th class="text-center">ราคาต่อชิ้น</th>
                                    <th class="text-center">จำนวน</th>
                                    <th class="text-end">รวม</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="../assets/images/<?php echo !empty($item['image']) ? $item['image'] : 'no-image.jpg'; ?>" 
                                                 width="50" height="50" class="me-3 rounded border border-secondary object-fit-cover">
                                            <span class="fw-bold"><?php echo $item['name']; ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center">฿<?php echo number_format($item['price'], 2); ?></td>
                                    <td class="text-center">x <?php echo $item['quantity']; ?></td>
                                    <td class="text-end fw-bold" style="color: var(--neon-blue);">฿<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot style="background: rgba(0, 210, 255, 0.05);">
                                <tr>
                                    <td colspan="3" class="text-end fw-bold py-3">ยอดสุทธิรวมทั้งสิ้น</td>
                                    <td class="text-end fw-bold py-3 fs-4" style="color: #00ff88;">
                                        ฿<?php echo number_format($order['total_price'], 2); ?>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                
                <div class="mt-4 text-end">
                    <button onclick="window.print()" class="btn btn-neon-blue px-4 py-2 rounded-pill" 
                            style="background: var(--accent-blue); color: white; border: none;">
                        <i class="fas fa-print me-2"></i>พิมพ์ใบปะหน้าพัสดุ
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>