<?php 
// 1. เริ่ม Session (สำคัญมาก! ของเดิมไม่มีจะเช็คสิทธิ์ไม่ได้)
session_start();

// เปิดแสดง Error สำหรับตรวจสอบ
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/db.php'; 

// ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); 
    exit();
}

/* ===========================
   ตัวแปรเริ่มต้น
=========================== */
$id = "";
$name = "";
$price = "";
$description = "";
$image = "";
$category_id = "";
$title_page = "เพิ่มสินค้าใหม่";

/* ===========================
   โหมดแก้ไข: ดึงข้อมูลเก่า
=========================== */
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $title_page = "แก้ไขข้อมูลสินค้า";

    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if ($product) {
        $name = $product['name'];
        $price = $product['price'];
        $description = $product['description'];
        $image = $product['image'];
        $category_id = $product['category_id'];
    }
}

/* ===========================
   ดึงรายชื่อหมวดหมู่
=========================== */
$cat_stmt = $conn->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $cat_stmt->fetchAll();

/* ===========================
   IMPORT CSV
=========================== */
if (isset($_POST['import_csv'])) {
    if (isset($_FILES['csv_file']['tmp_name']) && $_FILES['csv_file']['tmp_name'] != "") {
        $file = fopen($_FILES['csv_file']['tmp_name'], "r");
        fgetcsv($file); // ข้าม Header

        while (($row = fgetcsv($file)) !== false) {
            $cat_name    = trim($row[0]);
            $p_name      = trim($row[1]);
            $p_desc      = trim($row[2]);
            $p_price     = trim($row[3]);

            if (empty($p_name)) continue;

            // หา category_id หรือสร้างใหม่
            $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ?");
            $stmt->execute([$cat_name]);
            $cat = $stmt->fetch();

            if ($cat) {
                $c_id = $cat['id'];
            } else {
                $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
                $stmt->execute([$cat_name ?: "ทั่วไป"]);
                $c_id = $conn->lastInsertId();
            }

            // Insert สินค้า
            $stmt = $conn->prepare("INSERT INTO products (name, price, description, image, category_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$p_name, $p_price ?: 0, $p_desc, "no-image.jpg", $c_id]);
        }
        fclose($file);
        header("Location: manage_products.php?import=success");
        exit();
    }
}

/* ===========================
   บันทึกข้อมูลสินค้า (INSERT / UPDATE)
=========================== */
if (isset($_POST['save_product'])) {
    $id = $_POST['id'];
    $old_image = $_POST['old_image'];
    $name = $_POST['name'] ?: "สินค้าไม่มีชื่อ";
    $price = $_POST['price'] ?: 0;
    $description = $_POST['description'];
    $cat_id = $_POST['category_id'] ?: null;

    $new_filename = $old_image ?: "no-image.jpg";

    if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != "") {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $new_filename = time() . "_" . uniqid() . "." . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], "../assets/images/" . $new_filename);
    }

    try {
        if ($id != "") {
            $sql = "UPDATE products SET name=?, price=?, description=?, image=?, category_id=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$name, $price, $description, $new_filename, $cat_id, $id]);
        } else {
            $sql = "INSERT INTO products (name, price, description, image, category_id) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$name, $price, $description, $new_filename, $cat_id]);
        }
        header("Location: manage_products.php");
        exit();
    } catch(PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title><?php echo $title_page; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-dark: #0f121a;
            --card-bg: #1a1d26;
            --neon-blue: #00d2ff;
            --accent-green: #00ff88;
            --border-color: #343a40;
        }

        body {
            background-color: var(--bg-dark);
            color: #e0e0e0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        .form-label { color: var(--neon-blue); font-weight: 500; }

        .form-control, .form-select {
            background-color: #0f121a;
            border: 1px solid var(--border-color);
            color: #fff;
        }

        .form-control:focus, .form-select:focus {
            background-color: #161a24;
            border-color: var(--neon-blue);
            color: #fff;
            box-shadow: 0 0 8px rgba(0, 210, 255, 0.2);
        }

        .btn-neon-save {
            background: linear-gradient(45deg, #00b09b, #96c93d);
            border: none; color: white; font-weight: bold;
            transition: 0.3s;
        }

        .btn-neon-save:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0, 255, 136, 0.3); color: #fff; }

        .import-section {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 10px;
            border: 1px dashed var(--border-color);
        }

        .preview-img {
            max-width: 150px;
            border: 2px solid var(--neon-blue);
            border-radius: 10px;
            padding: 5px;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold m-0"><i class="fas fa-edit me-2" style="color: var(--neon-blue);"></i><?php echo $title_page; ?></h2>
                <a href="manage_products.php" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="fas fa-arrow-left me-2"></i>ย้อนกลับ
                </a>
            </div>

            <div class="main-card p-4 p-md-5">
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <input type="hidden" name="old_image" value="<?php echo $image; ?>">

                    <div class="row g-4">
                        <div class="col-md-12">
                            <label class="form-label">ชื่อสินค้า</label>
                            <input type="text" name="name" class="form-control form-control-lg" placeholder="ระบุชื่อสินค้า..." value="<?php echo $name; ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">หมวดหมู่</label>
                            <select name="category_id" class="form-select">
                                <option value="">-- เลือกหมวดหมู่ --</option>
                                <?php foreach($categories as $c): ?>
                                    <option value="<?php echo $c['id']; ?>" <?php echo ($category_id == $c['id']) ? 'selected' : ''; ?>>
                                        <?php echo $c['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">ราคา (บาท)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-white">฿</span>
                                <input type="number" step="0.01" name="price" class="form-control" placeholder="0.00" value="<?php echo $price; ?>" required>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">รายละเอียดสินค้า</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="บอกรายละเอียดสินค้าคร่าวๆ..."><?php echo $description; ?></textarea>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">รูปภาพสินค้า</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <?php if($image): ?>
                                <div class="mt-3">
                                    <p class="small text-muted mb-2">รูปภาพปัจจุบัน:</p>
                                    <img src="../assets/images/<?php echo $image; ?>" class="preview-img" alt="Product Preview">
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-12 mt-5">
                            <button type="submit" name="save_product" class="btn btn-neon-save btn-lg w-100 py-3 shadow">
                                <i class="fas fa-save me-2"></i> บันทึกข้อมูลสินค้า
                            </button>
                        </div>
                    </div>
                </form>

                <div class="import-section p-4 mt-5">
                    <h5 class="fw-bold mb-3"><i class="fas fa-file-import me-2 text-warning"></i>นำเข้าสินค้าแบบกลุ่ม (CSV)</h5>
                    <form action="" method="POST" enctype="multipart/form-data" class="row g-3">
                        <div class="col-sm-9">
                            <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                        </div>
                        <div class="col-sm-3">
                            <button type="submit" name="import_csv" class="btn btn-primary w-100">Import</button>
                        </div>
                    </form>
                    <div class="mt-3 p-3 rounded bg-black text-info small">
                        <i class="fas fa-info-circle me-1"></i> <b>Format:</b> category, name, description, price (เรียงลำดับตามนี้)
                    </div>
                </div>

            </div> <p class="text-center mt-4 text-muted small">ระบบจัดการสินค้าเวอร์ชัน 2026 • Admin Dashboard</p>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>