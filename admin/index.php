<?php 
require_once '../config/db.php'; 

// ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../user_interface/login.php");
    exit();
}

// ==========================================
// 1. ส่วนคำนวณตัวเลขการ์ด (Dashboard Cards)
// ==========================================

// ยอดขายรวม
$stmt = $conn->query("SELECT SUM(total_price) FROM orders WHERE status != 'cancelled'");
$total_sales = $stmt->fetchColumn() ?: 0;

// ออเดอร์รอจัดการ
$stmt = $conn->query("SELECT COUNT(*) FROM orders WHERE status IN ('pending', 'preparing')");
$pending_orders = $stmt->fetchColumn();

// จำนวนสินค้า
$stmt = $conn->query("SELECT COUNT(*) FROM products");
$total_products = $stmt->fetchColumn();

// จำนวนลูกค้า
$stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
$total_users = $stmt->fetchColumn();


// ==========================================
// 2. ส่วนดึงข้อมูลเพื่อทำกราฟ (Charts Data)
// ==========================================

// --- กราฟที่ 1: ยอดขายแยกตามหมวดหมู่ (Category Sales) ---
// ต้อง Join: order_details -> products -> categories
// สมมติว่าตารางเก็บรายการสั่งซื้อชื่อ 'order_details' (หรือ order_items ตามที่คุณใช้)
// ถ้าชื่อตารางคุณต่างจากนี้ ให้แก้ตรง FROM order_details นะครับ
$sql_cat = "SELECT c.name, SUM(od.price * od.quantity) as total_sold
            FROM order_details od
            JOIN products p ON od.product_id = p.id
            JOIN categories c ON p.category_id = c.id
            JOIN orders o ON od.order_id = o.id
            WHERE o.status != 'cancelled'
            GROUP BY c.id";
$stmt_cat = $conn->query($sql_cat);
$cat_data = $stmt_cat->fetchAll();

// เตรียม array ไว้ส่งให้ JavaScript
$cat_labels = [];
$cat_sales = [];
foreach ($cat_data as $row) {
    $cat_labels[] = $row['name'];
    $cat_sales[] = $row['total_sold'];
}

// --- กราฟที่ 2: 5 อันดับสินค้าขายดี (Top 5 Best Sellers) ---
$sql_top = "SELECT p.name, SUM(od.quantity) as total_qty
            FROM order_details od
            JOIN products p ON od.product_id = p.id
            JOIN orders o ON od.order_id = o.id
            WHERE o.status != 'cancelled'
            GROUP BY p.id
            ORDER BY total_qty DESC
            LIMIT 5";
$stmt_top = $conn->query($sql_top);
$top_data = $stmt_top->fetchAll();

$top_labels = [];
$top_qty = [];
foreach ($top_data as $row) {
    $top_labels[] = $row['name'];
    $top_qty[] = $row['total_qty'];
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ภาพรวมร้านค้า - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .hover-card {
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .hover-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15) !important;
        }
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
    </style>
</head>
<body class="bg-light">

    <div class="d-flex">
        <div class="bg-dark text-white p-3 vh-100 sticky-top" style="width: 250px;">
            <h4 class="text-center">Admin Panel</h4>
            <hr>
            <ul class="nav flex-column gap-2">
                <li><a href="index.php" class="nav-link active bg-primary text-white rounded">📊 ภาพรวม</a></li>
                <li><a href="manage_products.php" class="nav-link text-white-50">📦 จัดการสินค้า</a></li>
                <li><a href="manage_categories.php" class="nav-link text-white-50">📂 จัดการประเภท</a></li>
                <li><a href="manage_orders.php" class="nav-link text-white-50">🚚 จัดการออเดอร์</a></li>
                <li><a href="manage_users.php" class="nav-link text-white-50">👥 จัดการลูกค้า</a></li>
                <li class="mt-4"><a href="logout.php" class="nav-link text-danger">ออกจากระบบ</a></li>
            </ul>
        </div>

        <div class="container-fluid p-4">
            <h2 class="mb-4">📊 ภาพรวมร้านค้า (Dashboard)</h2>

            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <a href="manage_orders.php" class="hover-card">
                        <div class="card bg-success text-white shadow-sm border-0 h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div><h6 class="card-title mb-0">ยอดขายรวม (บาท)</h6><h2 class="mt-2 fw-bold">฿<?php echo number_format($total_sales, 2); ?></h2></div>
                                <i class="fas fa-money-bill-wave fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="manage_orders.php" class="hover-card">
                        <div class="card bg-warning text-dark shadow-sm border-0 h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div><h6 class="card-title mb-0">รอดำเนินการ (ออเดอร์)</h6><h2 class="mt-2 fw-bold"><?php echo number_format($pending_orders); ?></h2></div>
                                <i class="fas fa-clock fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="manage_products.php" class="hover-card">
                        <div class="card bg-primary text-white shadow-sm border-0 h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div><h6 class="card-title mb-0">สินค้าทั้งหมด (ชิ้น)</h6><h2 class="mt-2 fw-bold"><?php echo number_format($total_products); ?></h2></div>
                                <i class="fas fa-box-open fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="manage_users.php" class="hover-card">
                        <div class="card bg-secondary text-white shadow-sm border-0 h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div><h6 class="card-title mb-0">ลูกค้าทั้งหมด (คน)</h6><h2 class="mt-2 fw-bold"><?php echo number_format($total_users); ?></h2></div>
                                <i class="fas fa-users fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white fw-bold">
                            <i class="fas fa-chart-pie text-primary"></i> สัดส่วนยอดขายตามหมวดหมู่
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="categoryChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white fw-bold">
                            <i class="fas fa-chart-bar text-success"></i> 5 อันดับสินค้าขายดี (จำนวนชิ้น)
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="topProductChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-info border-0 shadow-sm" role="alert">
                <i class="fas fa-info-circle"></i> ข้อมูลกราฟจะอัปเดตอัตโนมัติเมื่อมีการสั่งซื้อและการจัดส่งสำเร็จ
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // --- รับข้อมูลจาก PHP มาเป็น JavaScript Variable ---
        
        // ข้อมูลกราฟหมวดหมู่
        const catLabels = <?php echo json_encode($cat_labels); ?>;
        const catData = <?php echo json_encode($cat_sales); ?>;

        // ข้อมูลกราฟสินค้าขายดี
        const topLabels = <?php echo json_encode($top_labels); ?>;
        const topData = <?php echo json_encode($top_qty); ?>;

        // --- 1. สร้างกราฟวงกลม (หมวดหมู่) ---
        const ctxCat = document.getElementById('categoryChart').getContext('2d');
        new Chart(ctxCat, {
            type: 'doughnut', // หรือเปลี่ยนเป็น 'pie'
            data: {
                labels: catLabels,
                datasets: [{
                    label: 'ยอดขาย (บาท)',
                    data: catData,
                    backgroundColor: [
                        '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'
                    ],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // --- 2. สร้างกราฟแท่ง (สินค้าขายดี) ---
        const ctxTop = document.getElementById('topProductChart').getContext('2d');
        new Chart(ctxTop, {
            type: 'bar',
            data: {
                labels: topLabels,
                datasets: [{
                    label: 'จำนวนที่ขายได้ (ชิ้น)',
                    data: topData,
                    backgroundColor: '#36b9cc',
                    borderColor: '#2c9faf',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</body>
</html>