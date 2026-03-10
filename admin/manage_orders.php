<?php 
require_once '../config/db.php'; 

// --- Logic เดิม ห้ามเปลี่ยน ---
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../user_interface/login.php");
    exit();
}

$status_options = [
    'pending' => '⏳ รอตรวจสอบการชำระเงิน',
    'paid' => '💰 ชำระเงินสำเร็จ',
    'delivered' => '✅ จัดส่งสำเร็จ'
];

if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    if ($stmt->execute([$status, $order_id])) {
        $msg = "✅ อัปเดตสถานะออเดอร์ #$order_id เป็น '" . $status_options[$status] . "' แล้ว";
    }
}

if (isset($_POST['cancel_order'])) {
    $order_id = $_POST['order_id'];
    $stmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
    if ($stmt->execute([$order_id])) {
        $msg = "⚠️ ยกเลิก (ลบ) คำสั่งซื้อ #$order_id เรียบร้อยแล้ว (แจ้งเตือนไปยังลูกค้าแล้ว)";
    }
}

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
    <title>จัดการออเดอร์ - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    
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
            overflow-x: hidden;
        }

        /* Sidebar */
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
            box-shadow: 0 0 15px rgba(83, 83, 255, 0.4);
        }

        /* Layout & Cards */
        .main-container { 
            padding: 40px; 
            width: 100%; 
        }
        
        .content-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.5);
        }

        /* Table สไตล์ที่สมดุล */
        .table { 
            color: #fff; 
            border-color: var(--border-color);
            table-layout: fixed; /* ทำให้ width คงที่ */
            width: 100%;
        }
        
        .table-light { 
            background: rgba(255,255,255,0.05) !important; 
            color: var(--neon-blue) !important; 
            border: none; 
        }
        
        .table-hover tbody tr:hover { 
            background: rgba(255,255,255,0.03); 
            transition: 0.2s; 
        }

        /* ปรับตัวหนังสือในตารางให้ชัดเจน */
        .table tbody tr td {
            color: #ffffff !important;
            font-weight: 500;
            vertical-align: middle;
            padding: 15px 8px;
            word-wrap: break-word;
        }

        /* เน้นชื่อลูกค้าให้เด่นขึ้น */
        .table tbody tr td .fw-bold {
            color: #ffffff !important;
            font-size: 1.05rem;
        }

        /* ปรับที่อยู่ให้ชัดขึ้น */
        .text-gray, .text-muted, small {
            color: #cccccc !important;
            font-weight: 400;
        }

        /* ปรับหัวตารางให้ดูคมชัด */
        .table-light th {
            color: #00d2ff !important;
            background-color: rgba(0, 0, 0, 0.1) !important;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid var(--border-color) !important;
            padding: 15px 8px;
            font-weight: 600;
        }

        /* ID Column */
        .id-column {
            color: var(--neon-blue) !important;
            font-weight: bold;
        }

        /* Price Column */
        .price-column {
            color: #00ff88 !important;
            font-weight: bold;
        }

        /* Custom Dropdown สไตล์ที่สมดุล */
        .select-container {
            width: 100%;
            max-width: 200px;
            cursor: pointer;
            position: relative;
            transition: 300ms;
            color: white;
        }

        .selected-box {
            background-color: #2a2f3b;
            padding: 8px 12px;
            border-radius: 5px;
            position: relative;
            z-index: 10;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            border: 1px solid rgba(255,255,255,0.1);
            min-height: 38px;
        }

        .arrow-icon {
            height: 10px;
            width: 20px;
            fill: white;
            transition: 300ms;
            transform: rotate(-90deg);
            flex-shrink: 0;
        }

        .options-list {
            display: flex;
            flex-direction: column;
            border-radius: 5px;
            padding: 5px;
            background-color: #2a2f3b;
            position: absolute;
            top: 100%;
            left: 0;
            opacity: 0;
            visibility: hidden;
            transition: 300ms;
            z-index: 100;
            width: 100%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.5);
            margin-top: 2px;
        }

        .select-container:hover .options-list {
            opacity: 1;
            visibility: visible;
        }

        .select-container:hover .arrow-icon {
            transform: rotate(0deg);
        }

        .option-item {
            border-radius: 5px;
            padding: 8px;
            transition: 300ms;
            background-color: #2a2f3b;
            font-size: 13px;
            width: 100%;
            text-align: left;
            border: none;
            color: white;
        }

        .option-item:hover {
            background-color: #323741;
            color: var(--neon-blue);
        }

        /* ปรับปุ่มให้สมดุล */
        .btn-outline-light {
            border: 1px solid #6c757d !important;
            color: #ffffff !important;
            background-color: transparent !important;
            font-weight: 600 !important;
            transition: all 0.2s ease-in-out;
            font-size: 0.85rem;
        }

        .btn-outline-light:hover {
            background-color: #6c757d !important;
            color: #ffffff !important;
            box-shadow: 0 0 10px rgba(108, 117, 125, 0.3);
        }

        .btn-outline-danger {
            font-size: 0.85rem;
            min-width: 38px;
        }

        /* Action Column */
        .action-column {
            min-width: 280px;
        }

        .action-flex {
            display: flex;
            gap: 8px;
            align-items: flex-start;
            justify-content: flex-start;
        }

        /* Tool Column */
        .tool-column {
            min-width: 120px;
        }

        .badge { 
            font-weight: 500; 
            padding: 0.5em 0.8em;
            font-size: 0.8rem;
        }

        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .selected-box {
                font-size: 12px;
                padding: 6px 10px;
            }
            
            .option-item {
                font-size: 12px;
                padding: 6px;
            }
        }
        /* ปรับตัวหนังสือในตารางให้เป็นสีขาวชัดเจน */
    .table tbody tr td {
        color: #060606 !important; /* บังคับสีขาว */
        font-weight: 500;
        vertical-align: middle;
        padding: 15px 8px;
        word-wrap: break-word;
    }

    /* ชื่อลูกค้า */
    .table tbody tr td .fw-bold {
        color: #050505 !important; /* บังคับสีขาว */
        font-size: 1.05rem;
    }

    /* ที่อยู่และข้อความเสริม */
    .text-gray, .text-muted, small {
        color: #0f0f0f !important; /* สีเทาอ่อน */
        font-weight: 400;
    }

    /* ID Column */
    .id-column {
        color: #00d2ff !important; /* สีฟ้า Neon */
        font-weight: bold;
    }

    /* Price Column */
    .price-column {
        color: #00ff88 !important; /* สีเขียว */
        font-weight: bold;
    }

    /* ข้อความทั่วไปในตาราง */
    .table td, .table th {
        color: #c4c2c2 !important;
    }

    /* ป้องกัน Bootstrap override */
    .table-hover > tbody > tr:hover > td {
        color: #e2dede !important;
    }

    /* ข้อความใน dropdown */
    .selected-box span {
        color: #ffffff !important;
    }

    /* ข้อความปิดรายการ */
    .text-muted.small.fst-italic {
        color: #999999 !important;
    }

    /* ปุ่มรายละเอียด */
    .btn-outline-light {
        border: 1px solid #6c757d !important;
        color: #6e2bf4 !important;
        background-color: rgba(108, 117, 125, 0.1) !important;
        font-weight: 600 !important;
        transition: all 0.2s ease-in-out;
        font-size: 0.85rem;
    }

    .btn-outline-light:hover {
        background-color: #6c757d !important;
        color: #ffffff !important;
        box-shadow: 0 0 10px rgba(108, 117, 125, 0.3);
    }

    /* เพิ่มการป้องกัน Bootstrap */
    .table-responsive .table tbody tr td * {
        color: inherit !important;
    }

    /* DataTables Custom Styling */
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        background: transparent !important;
        border: 1px solid var(--border-color) !important;
        color: var(--text-gray) !important;
        padding: 6px 12px !important;
        margin: 2px !important;
        border-radius: 5px !important;
        transition: all 0.3s !important;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: var(--accent-blue) !important;
        color: #fff !important;
        box-shadow: 0 0 10px rgba(83, 83, 255, 0.3) !important;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.active {
        background: var(--accent-blue) !important;
        color: #fff !important;
        box-shadow: 0 0 15px rgba(83, 83, 255, 0.4) !important;
    }
    
    .dataTables_wrapper .dataTables_filter input {
        background: rgba(0,0,0,0.2) !important;
        border: 1px solid var(--border-color) !important;
        color: #fff !important;
        padding: 8px 12px !important;
        border-radius: 5px !important;
    }
    
    .dataTables_wrapper .dataTables_filter input:focus {
        background: rgba(0,0,0,0.3) !important;
        border-color: var(--neon-blue) !important;
        box-shadow: 0 0 10px rgba(0,210,255,0.2) !important;
        color: #fff !important;
    }
    
    .dataTables_wrapper .dataTables_length select {
        background: rgba(0,0,0,0.2) !important;
        border: 1px solid var(--border-color) !important;
        color: #fff !important;
        padding: 6px 10px !important;
        border-radius: 5px !important;
    }
    
    .dataTables_wrapper .dataTables_info {
        color: var(--text-gray) !important;
    }

    </style>
</head>
<body>

    <div class="d-flex">
        <div class="sidebar p-4 vh-100 sticky-top">
            <h3 class="text-center fw-bold mb-4" style="color: var(--neon-blue); text-shadow: 0 0 10px rgba(0,210,255,0.3);">
                <i class="fas fa-user-shield me-2"></i>ADMIN
            </h3>
            <hr class="border-secondary mb-4">
            <ul class="nav flex-column">
                <li><a href="index.php" class="nav-link"><i class="fas fa-chart-line me-2"></i> ภาพรวม</a></li>
                <li><a href="manage_products.php" class="nav-link"><i class="fas fa-box-open me-2"></i> จัดการสินค้า</a></li>
                <li><a href="manage_categories.php" class="nav-link"><i class="fas fa-folder me-2"></i> จัดการประเภท</a></li>
                <li><a href="manage_orders.php" class="nav-link active"><i class="fas fa-shopping-cart me-2"></i> จัดการออเดอร์</a></li>
                <li><a href="manage_users.php" class="nav-link"><i class="fas fa-users me-2"></i> จัดการลูกค้า</a></li>
                <li class="mt-4">
                    <a href="../user_interface/index.php" class="nav-link" style="color: var(--neon-blue) !important;" target="_blank">
                        <i class="fas fa-external-link-alt me-2"></i> ไปหน้าร้านค้า
                    </a>
                </li>
                <li class="mt-2"><a href="logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt me-2"></i> ออกจากระบบ</a></li>
            </ul>
        </div>

        <div class="main-container">
            <h2 class="fw-bold mb-4" style="letter-spacing: 2px;">ORDER <span style="color: var(--neon-blue);">MANAGEMENT</span></h2>

            <?php if(isset($msg)): ?>
                <div class="alert alert-info alert-dismissible fade show border-0 bg-primary text-white shadow" role="alert">
                    <i class="fas fa-info-circle me-2"></i> <?php echo $msg; ?>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="content-card">
                <div class="table-responsive">
                    <table id="ordersTable" class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="8%">#ID</th>
                                <th width="22%">ข้อมูลลูกค้า</th>
                                <th width="12%">ยอดรวม</th>
                                <th width="15%">สถานะปัจจุบัน</th>
                                <th width="30%">จัดการ / เปลี่ยนสถานะ</th>
                                <th width="13%" class="text-center">เครื่องมือ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($orders as $order): ?>
                            <tr>
                                <td class="id-column">#<?php echo $order['id']; ?></td>
                                
                                <td>
                                    <div class="fw-bold mb-1"><?php echo htmlspecialchars($order['fullname']); ?></div>
                                    <small class="text-gray" style="font-size: 0.8rem;">
                                        <i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars(mb_strimwidth($order['address'], 0, 40, '...')); ?>
                                    </small>
                                </td>

                                <td class="price-column">฿<?php echo number_format($order['total_price'], 2); ?></td>

                                <td>
                                    <?php 
                                        $s = $order['status'];
                                        switch ($s) {
                                            case 'pending': 
                                                echo '<span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> รอตรวจสอบ</span>'; 
                                                break;
                                            case 'paid': 
                                                echo '<span class="badge bg-info text-dark"><i class="fas fa-check-circle me-1"></i> ชำระแล้ว</span>'; 
                                                break;
                                            case 'delivered': 
                                                echo '<span class="badge bg-success"><i class="fas fa-truck me-1"></i> จัดส่งแล้ว</span>'; 
                                                break;
                                            case 'cancelled': 
                                                echo '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> ยกเลิกแล้ว</span>'; 
                                                break;
                                            default: 
                                                echo '<span class="badge bg-secondary">'. htmlspecialchars($s) .'</span>';
                                        }
                                    ?>
                                </td>

                                <td class="action-column">
                                    <?php if($order['status'] !== 'cancelled'): ?>
                                        <div class="action-flex">
                                            <div class="select-container">
                                                <div class="selected-box">
                                                    <span><?php echo $status_options[$order['status']]; ?></span>
                                                    <svg class="arrow-icon" viewBox="0 0 448 512">
                                                        <path d="M201.4 342.6c12.5 12.5 32.8 12.5 45.3 0l160-160c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L224 274.7 86.6 137.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l160 160z"/>
                                                    </svg>
                                                </div>
                
                                                <div class="options-list">
                                                    <?php foreach($status_options as $key => $label): ?>
                                                        <form action="manage_orders.php" method="POST" style="margin: 0;">
                                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                            <input type="hidden" name="status" value="<?php echo $key; ?>">
                                                            <button type="submit" name="update_status" class="option-item">
                                                                <?php echo $label; ?>
                                                            </button>
                                                        </form>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            
                                            <form action="manage_orders.php" method="POST" onsubmit="return confirm('⚠️ ยืนยันการลบออเดอร์นี้?');">
                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                <button type="submit" name="cancel_order" class="btn btn-sm btn-outline-danger" style="height: 38px;">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small fst-italic">
                                            <i class="fas fa-ban me-1"></i> ปิดรายการแล้ว
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td class="text-center tool-column">
                                    <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-light">
                                        <i class="fas fa-search me-1"></i> รายละเอียด
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
    
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <script>
    $(document).ready(function() {
        $('#ordersTable').DataTable({
            responsive: true,
            lengthChange: true,
            searching: true,
            ordering: true,
            paging: true,
            info: true,
            pageLength: 10,
            language: {
                "sEmptyTable": "ไม่พบข้อมูลออเดอร์",
                "sInfo": "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                "sInfoEmpty": "แสดง 0 ถึง 0 จากทั้งหมด 0 รายการ",
                "sInfoFiltered": "(กรองจากทั้งหมด _MAX_ รายการ)",
                "sLengthMenu": "แสดง _MENU_ รายการ",
                "sLoadingRecords": "กำลังโหลด...",
                "sProcessing": "กำลังประมวลผล...",
                "sSearch": "<i class='fas fa-search'></i> ค้นหา:",
                "sZeroRecords": "ไม่พบข้อมูลที่ตรงกัน",
                "oPaginate": {
                    "sFirst": "หน้าแรก",
                    "sLast": "หน้าสุดท้าย",
                    "sNext": "ถัดไป",
                    "sPrevious": "ก่อนหน้า"
                }
            },
            dom: '<"row"<"col-md-6"l><"col-md-6"f>>' +
                 '<"row"<"col-12"tr>>' +
                 '<"row"<"col-md-6"i><"col-md-6"p>>',
            columnDefs: [
                {
                    targets: [4, 5],
                    orderable: false,
                    searchable: false
                }
            ]
        });
    });
    </script>
</body>
</html>
