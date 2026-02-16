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
        /* ตัวแปรสีธีม Cyberpunk */
        :root {
            --bg-dark: #0f121a;
            --card-bg: rgba(36, 40, 50, 0.9);
            --accent-blue: #5353ff;
            --neon-blue: #00d2ff;
            --neon-pink: #ff5353;
            --text-gray: #7e8590;
            --border-color: #42434a;
        }

        body {
            background-color: var(--bg-dark);
            color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }

        /* Sidebar สไตล์ล้ำๆ */
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
            transform: translateX(5px);
            box-shadow: 0 0 15px rgba(83, 83, 255, 0.4);
        }

        /* Dashboard Cards (Glassmorphism) */
        .hover-card {
            text-decoration: none;
            display: block;
            height: 100%;
        }

        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            padding: 25px;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            border-color: var(--neon-blue);
            box-shadow: 0 10px 30px rgba(0, 210, 255, 0.1);
        }

        .stat-card::after {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(0, 210, 255, 0.05) 0%, transparent 70%);
            pointer-events: none;
        }

        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
            color: var(--neon-blue);
        }

        /* Chart Containers */
        .chart-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            padding: 20px;
        }

        .card-header {
            background: transparent !important;
            border-bottom: 1px solid var(--border-color) !important;
            color: var(--neon-blue);
            font-size: 1.1rem;
            letter-spacing: 1px;
        }

        /* ปรับแต่ง Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: var(--bg-dark); }
        ::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--accent-blue); }

        .alert-info {
            background: rgba(83, 83, 255, 0.1);
            border: 1px solid var(--accent-blue);
            color: var(--neon-blue);
        }
    </style>
</head>
<body class="bg-dark">
    <div class="d-flex">
        <div class="sidebar p-4 vh-100 sticky-top">
            <h3 class="text-center fw-bold mb-4" style="color: var(--neon-blue); text-shadow: 0 0 10px rgba(0,210,255,0.3);">
                <i class="fas fa-user-shield me-2"></i>ADMIN
            </h3>
            <hr class="border-secondary mb-4">
            <ul class="nav flex-column">
                <li><a href="index.php" class="nav-link active"><i class="fas fa-chart-line me-2"></i> ภาพรวม</a></li>
                <li><a href="manage_products.php" class="nav-link"><i class="fas fa-box-open me-2"></i> จัดการสินค้า</a></li>
                <li><a href="manage_categories.php" class="nav-link"><i class="fas fa-folder me-2"></i> จัดการประเภท</a></li> 
                <li><a href="manage_orders.php" class="nav-link"><i class="fas fa-shopping-cart me-2"></i> จักการออเดอร์</a></li>
                <li><a href="manage_users.php" class="nav-link"><i class="fas fa-users me-2"></i> จัดการลูกค้า</a></li>
                <li class="mt-4">
                    <a href="../user_interface/index.php" class="nav-link" style="color: var(--neon-blue) !important;" target="_blank">
                        <i class="fas fa-external-link-alt me-2"></i> ไปหน้าร้านค้า
                    </a>
                </li>
                <li class="mt-2"><a href="logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt me-2"></i> ออกจากระบบ</a></li>
            </ul>
        </div>

        <div class="container-fluid p-5">
            <h2 class="mb-5 fw-bold" style="letter-spacing: 2px;">DASHBOARD <span style="color: var(--neon-blue);">OVERVIEW</span></h2>

            <div class="row g-4 mb-5">
                <div class="col-md-3">
                    <a href="manage_orders.php" class="hover-card">
                        <div class="stat-card d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-gray mb-1">ยอดขายรวม (บาท)</h6>
                                <h3 class="fw-bold mb-0">฿<?php echo number_format($total_sales, 2); ?></h3>
                            </div>
                            <i class="fas fa-money-bill-wave stat-icon"></i>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="manage_orders.php" class="hover-card">
                        <div class="stat-card d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-gray mb-1">รอดำเนินการ</h6>
                                <h3 class="fw-bold mb-0"><?php echo number_format($pending_orders); ?></h3>
                            </div>
                            <i class="fas fa-clock stat-icon" style="color: var(--neon-pink);"></i>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="manage_products.php" class="hover-card">
                        <div class="stat-card d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-gray mb-1">สินค้าทั้งหมด</h6>
                                <h3 class="fw-bold mb-0"><?php echo number_format($total_products); ?></h3>
                            </div>
                            <i class="fas fa-box-open stat-icon"></i>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="manage_users.php" class="hover-card">
                        <div class="stat-card d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-gray mb-1">ลูกค้าทั้งหมด</h6>
                                <h3 class="fw-bold mb-0"><?php echo number_format($total_users); ?></h3>
                            </div>
                            <i class="fas fa-users stat-icon"></i>
                        </div>
                    </a>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="chart-card">
                        <div class="card-header pb-3 mb-3">
                            <i class="fas fa-chart-pie me-2"></i> สัดส่วนยอดขายตามหมวดหมู่
                        </div>
                        <div style="height: 300px;">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="chart-card">
                        <div class="card-header pb-3 mb-3">
                            <i class="fas fa-chart-bar me-2"></i> 5 อันดับสินค้าขายดี
                        </div>
                        <div style="height: 300px;">
                            <canvas id="topProductChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-info border-0 shadow-sm" role="alert">
                <i class="fas fa-info-circle me-2"></i> ข้อมูลกราฟจะอัปเดตอัตโนมัติเมื่อมีการสั่งซื้อและการจัดส่งสำเร็จ
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
            type: 'doughnut',
            data: {
                labels: catLabels,
                datasets: [{
                    data: catData,
                    backgroundColor: [
                        '#5353ff', '#00d2ff', '#ff5353', '#f6c23e', '#1cc88a', '#858796'
                    ],
                    borderColor: '#1e2129',
                    borderWidth: 3,
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        position: 'bottom',
                        labels: { color: '#7e8590', padding: 20 }
                    }
                }
            }
        });

        // --- 2. สร้างกราฟแท่ง (สินค้าขายดี) ---
        const ctxTop = document.getElementById('topProductChart').getContext('2d');
        const gradient = ctxTop.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, '#00d2ff');
        gradient.addColorStop(1, '#5353ff');

        new Chart(ctxTop, {
            type: 'bar',
            data: {
                labels: topLabels,
                datasets: [{
                    label: 'จำนวนที่ขายได้ (ชิ้น)',
                    data: topData,
                    backgroundColor: gradient,
                    borderRadius: 8,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { 
                        beginAtZero: true,
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#7e8590' }
                    },
                    x: { 
                        grid: { display: false },
                        ticks: { color: '#7e8590' }
                    }
                },
                plugins: {
                    legend: { labels: { color: '#7e8590' } }
                }
            }
        });
    </script>
</body>
</html>