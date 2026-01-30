<?php
session_start();
require_once '../config/db.php';

// เช็ค Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // header("Location: ../login.php"); 
}

$msg = "";

// --- ส่วนบันทึกข้อมูลแบบหลายรายการ (Bulk Update) ---
if (isset($_POST['save_all'])) {
    $count = 0;
    // วนลูปตามข้อมูลที่ส่งมา (product_id => ข้อมูล)
    foreach ($_POST['products'] as $id => $data) {
        $price = $data['price'];
        $stock = $data['stock'];
        
        // อัปเดตเฉพาะ ราคา และ สต็อก
        $stmt = $conn->prepare("UPDATE products SET price = ?, stock = ? WHERE id = ?");
        $stmt->execute([$price, $stock, $id]);
        $count++;
    }
    $msg = "<div class='alert alert-success fixed-top m-3' style='z-index:9999;'>✅ บันทึกข้อมูล $count รายการ เรียบร้อยแล้ว!</div>";
}

// ดึงข้อมูลสินค้าทั้งหมด
$stmt = $conn->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เก็บงานสินค้า (Quick Edit)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .img-thumb { width: 50px; height: 50px; object-fit: cover; }
        .bg-error { background-color: #ffe6e6; } /* สีพื้นหลังแดงอ่อนๆ ถ้ารูปหาย */
    </style>
</head>
<body class="bg-light pb-5">

    <?php echo $msg; ?>

    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>🛠️ หน้าเก็บงานสินค้า (แก้ไขด่วน)</h3>
            <div>
                <a href="manage_products.php" class="btn btn-secondary">กลับหน้าหลัก</a>
                <button form="bulkForm" type="submit" name="save_all" class="btn btn-primary btn-lg shadow">
                    💾 บันทึกการแก้ไขทั้งหมด
                </button>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <form id="bulkForm" action="" method="post">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark sticky-top">
                            <tr>
                                <th width="5%">ID</th>
                                <th width="10%">สถานะรูป</th>
                                <th width="35%">ชื่อสินค้า</th>
                                <th width="15%">ราคา (บาท)</th>
                                <th width="15%">สต็อก (ชิ้น)</th>
                                <th width="10%">ชื่อไฟล์รูป</th>
                                <th width="10%">จัดการเต็ม</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($products as $p): ?>
                                <?php 
                                    // เช็คว่าไฟล์รูปมีจริงไหม?
                                    $img_path = "../assets/images/" . $p['image'];
                                    $file_exists = file_exists($img_path);
                                    $row_class = $file_exists ? "" : "bg-error"; // ถ้าไม่มีรูป ใส่สีแดงเตือน
                                ?>
                                <tr class="<?php echo $row_class; ?>">
                                    <td><?php echo $p['id']; ?></td>
                                    
                                    <td class="text-center">
                                        <?php if($file_exists): ?>
                                            <img src="<?php echo $img_path; ?>" class="img-thumb rounded">
                                        <?php else: ?>
                                            <span class="badge bg-danger">❌ ไม่พบไฟล์</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <strong><?php echo $p['name']; ?></strong>
                                        <?php if(!$file_exists): ?>
                                            <div class="text-danger small mt-1">
                                                *ระบบหาไฟล์ "<?php echo $p['image']; ?>" ไม่เจอ
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <input type="number" step="0.01" 
                                               name="products[<?php echo $p['id']; ?>][price]" 
                                               value="<?php echo $p['price']; ?>" 
                                               class="form-control text-end">
                                    </td>

                                    <td>
                                        <input type="number" 
                                               name="products[<?php echo $p['id']; ?>][stock]" 
                                               value="<?php echo $p['stock']; ?>" 
                                               class="form-control text-center">
                                    </td>

                                    <td class="small text-muted">
                                        <?php echo $p['image']; ?>
                                    </td>

                                    <td>
                                        <a href="product_form.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-dark" target="_blank">
                                            ✏️ แก้ละเอียด
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>

    <script>
        setTimeout(function() {
            let alert = document.querySelector('.alert');
            if(alert) alert.remove();
        }, 3000);
    </script>
</body>
</html>