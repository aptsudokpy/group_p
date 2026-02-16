<?php
session_start();
require_once '../config/db.php';

// ฟังก์ชันแก้ปัญหาภาษาไทย (Remove BOM)
function removeBOM($text) {
    $bom = pack('H*','EFBBBF');
    return preg_replace("/^$bom/", '', $text);
}

$msg = "";
$log = ""; // ตัวแปรเก็บ Log การทำงาน

if (isset($_POST['import'])) {
    if ($_FILES['file']['size'] > 0) {
        $file = fopen($_FILES['file']['tmp_name'], "r");

        // ข้ามบรรทัดหัวตาราง (Header)
        fgetcsv($file);

        $count = 0;
        $row_num = 1;

        while (($row = fgetcsv($file, 10000, ",")) !== FALSE) {
            $row_num++;

            // 1. เช็คว่าแถวนี้ว่างเปล่าหรือไม่? (ถ้าว่างข้ามไปเลย กัน Error)
            if (empty($row[0]) && empty($row[1])) {
                continue; 
            }

            // 2. ดึงข้อมูล (Mapping) ตามไฟล์ CSV
            // คอลัมน์ A: ประเภท
            $cat_raw  = isset($row[0]) ? trim($row[0]) : ""; 
            // คอลัมน์ B: ชื่อ
            $p_name   = isset($row[1]) ? trim($row[1]) : "สินค้าไม่ระบุชื่อ"; 
            // คอลัมน์ C: รายละเอียด
            $p_desc   = isset($row[2]) ? trim($row[2]) : ""; 
            // คอลัมน์ D: ราคา (ตัดลูกน้ำออก)
            $p_price  = isset($row[3]) ? str_replace(',', '', $row[3]) : 0; 
            
            // 🔥 [ส่วนที่เพิ่มใหม่] คอลัมน์ E: ชื่อไฟล์รูปภาพ
            // ถ้าใน Excel มีช่องที่ 5 ให้ใช้ค่าที่ใส่มา, ถ้าไม่มี หรือปล่อยว่าง ให้ใช้ "no-image.jpg"
            $p_image  = (isset($row[4]) && !empty(trim($row[4]))) ? trim($row[4]) : "no-image.jpg";

            // แก้ปัญหาภาษาไทยแถวแรก (Remove BOM) เฉพาะบรรทัดข้อมูลแรก
            if ($row_num == 2) { 
                $cat_raw = removeBOM($cat_raw); 
            }

            // ถ้าไม่มีประเภทสินค้า ให้ตั้งเป็น "สินค้าทั่วไป"
            if (empty($cat_raw)) { $cat_raw = "สินค้าทั่วไป"; }

            // 3. จัดการหมวดหมู่ (Category Logic) - เช็คว่ามีหมวดหมู่นี้หรือยัง
            $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ?");
            $stmt->execute([$cat_raw]);
            $cat_data = $stmt->fetch();

            if ($cat_data) {
                $cat_id = $cat_data['id']; // มีแล้ว ใช้ ID เดิม
            } else {
                // ยังไม่มี สร้างใหม่เลย
                $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
                $stmt->execute([$cat_raw]);
                $cat_id = $conn->lastInsertId();
            }

            // 4. บันทึกสินค้า (เพิ่ม $p_image ลงไป)
            // ใส่ Stock=100 เป็นค่าเริ่มต้น
            $sql = "INSERT INTO products (name, description, price, stock, category_id, image) 
                    VALUES (?, ?, ?, 100, ?, ?)"; // <-- เครื่องหมาย ? ตัวสุดท้ายคือรูปภาพ
            $stmt = $conn->prepare($sql);
            
            try {
                // ส่งค่าเรียงตามลำดับ: ชื่อ, รายละเอียด, ราคา, หมวดหมู่, รูปภาพ
                $stmt->execute([$p_name, $p_desc, $p_price, $cat_id, $p_image]);
                
                $count++;
                // เก็บ Log
                $log .= "<li>บรรทัด $row_num: เพิ่ม <strong>$p_name</strong> (รูป: $p_image) เรียบร้อย</li>";
            } catch (Exception $e) {
                $log .= "<li class='text-danger'>บรรทัด $row_num Error: " . $e->getMessage() . "</li>";
            }
        }
        
        fclose($file);
        $msg = "<div class='alert alert-success'>✅ นำเข้าสำเร็จ $count รายการ</div>";
    } else {
        $msg = "<div class='alert alert-danger'>❌ กรุณาเลือกไฟล์ก่อนกดปุ่ม</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Import Excel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4>📥 นำเข้าสินค้าจาก Excel (CSV) + รูปภาพ</h4>
            </div>
            <div class="card-body">
                
                <?php echo $msg; ?>

                <div class="row">
                    <div class="col-md-6">
                        <form action="" method="post" enctype="multipart/form-data" class="p-3 border rounded bg-white">
                            <label class="form-label fw-bold">เลือกไฟล์ CSV</label>
                            <input type="file" name="file" class="form-control mb-3" accept=".csv" required>
                            
                            <button type="submit" name="import" class="btn btn-success w-100">
                                <i class="fas fa-file-import"></i> เริ่มนำเข้าข้อมูล
                            </button>
                            <div class="mt-2 text-center">
                                <a href="manage_products.php" class="text-secondary small">กลับหน้าจัดการสินค้า</a>
                            </div>
                        </form>
                    </div>

                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <strong>รูปแบบไฟล์ที่รองรับ (5 คอลัมน์):</strong>
                            <ol>
                                <li>ประเภทสินค้า (ระบบแยกหมวดให้เอง)</li>
                                <li>ชื่อสินค้า</li>
                                <li>รายละเอียด</li>
                                <li>ราคา</li>
                                <li><strong>ชื่อไฟล์รูปภาพ</strong> (เช่น ak47.jpg)</li>
                            </ol>
                            <small class="text-muted">*อย่าลืมเอารูปไปใส่ไว้ใน folder <code>assets/images/</code> ด้วยนะครับ</small>
                        </div>
                    </div>
                </div>

                <?php if ($log != ""): ?>
                    <hr>
                    <h5>📊 รายการที่ดำเนินการ:</h5>
                    <div class="border p-3 bg-white" style="height: 200px; overflow-y: scroll;">
                        <ul class="mb-0 ps-3">
                            <?php echo $log; ?>
                        </ul>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</body>
</html>