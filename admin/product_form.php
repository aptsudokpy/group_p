<?php
require_once '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../user_interface/login.php");
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
    $title_page = "แก้ไขสินค้า";

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
   ดึงรายชื่อหมวดหมู่ทั้งหมด
=========================== */
$cat_stmt = $conn->query("SELECT * FROM categories");
$categories = $cat_stmt->fetchAll();


/* ===========================
   IMPORT CSV (เพิ่มใหม่)
=========================== */
if (isset($_POST['import_csv'])) {

    if (isset($_FILES['csv_file']['tmp_name']) && $_FILES['csv_file']['tmp_name'] != "") {

        $file = fopen($_FILES['csv_file']['tmp_name'], "r");

        // ข้าม header
        fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {

            // CSV Columns:
            // category,name,description,price
            $cat_name    = trim($row[0]);
            $name        = trim($row[1]);
            $description = trim($row[2]);
            $price       = trim($row[3]);

            // Default ถ้าว่าง
            if ($name == "") $name = "สินค้าไม่ระบุชื่อ";
            if ($price == "") $price = 0;
            if ($cat_name == "") $cat_name = "ไม่ระบุหมวดหมู่";

            // --- หา category_id ---
            $stmt = $conn->prepare("SELECT id FROM categories WHERE name=?");
            $stmt->execute([$cat_name]);
            $cat = $stmt->fetch();

            if ($cat) {
                $category_id = $cat['id'];
            } else {
                // ถ้าไม่มีหมวดหมู่ → เพิ่มใหม่
                $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
                $stmt->execute([$cat_name]);
                $category_id = $conn->lastInsertId();
            }

            // --- Insert Product ---
            $sql = "INSERT INTO products (name, price, description, image, category_id)
                    VALUES (?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $name,
                $price,
                $description,
                "no-image.jpg",
                $category_id
            ]);
        }

        fclose($file);

        header("Location: manage_products.php");
        exit();
    }
}


/* ===========================
   บันทึกข้อมูลสินค้า (เดิม)
=========================== */
if (isset($_POST['save_product'])) {

    $id = $_POST['id'];
    $old_image = $_POST['old_image'];

    // 1. ชื่อสินค้า
    $name = !empty($_POST['name']) ? $_POST['name'] : "สินค้าไม่ระบุชื่อ";

    // 2. ราคา
    $price = !empty($_POST['price']) ? $_POST['price'] : 0;

    // 3. รายละเอียด
    $description = $_POST['description'];

    // 4. หมวดหมู่
    $cat_id = !empty($_POST['category_id']) ? $_POST['category_id'] : 1;

    // จัดการรูปภาพ
    $new_filename = $old_image;

    if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != "") {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $new_filename = time() . "_" . uniqid() . "." . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], "../assets/images/" . $new_filename);
    }

    if (empty($new_filename)) {
        $new_filename = "no-image.jpg";
    }

    try {

        if ($id != "") {
            // UPDATE
            $sql = "UPDATE products 
                    SET name=?, price=?, description=?, image=?, category_id=? 
                    WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$name, $price, $description, $new_filename, $cat_id, $id]);

        } else {
            // INSERT
            $sql = "INSERT INTO products (name, price, description, image, category_id)
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$name, $price, $description, $new_filename, $cat_id]);
        }

        header("Location: manage_products.php");
        exit();

    } catch(PDOException $e) {
        echo "เกิดข้อผิดพลาด: " . $e->getMessage();
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title><?php echo $title_page; ?> - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><?php echo $title_page; ?></h4>
                    <a href="manage_products.php" class="btn btn-secondary btn-sm">ย้อนกลับ</a>
                </div>

                <div class="card-body p-4">

                    <!-- ===========================
                         ฟอร์มเพิ่ม/แก้ไขสินค้า
                    ============================ -->
                    <form action="" method="POST" enctype="multipart/form-data">

                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <input type="hidden" name="old_image" value="<?php echo $image; ?>">

                        <div class="mb-3">
                            <label class="form-label">ชื่อสินค้า</label>
                            <input type="text" name="name" class="form-control"
                                   value="<?php echo $name; ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">หมวดหมู่สินค้า</label>
                            <select name="category_id" class="form-select">
                                <option value="">-- ไม่ระบุ --</option>

                                <?php foreach($categories as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"
                                        <?php if($category_id == $c['id']) echo 'selected'; ?>>
                                        <?php echo $c['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">ราคา</label>
                            <input type="number" step="0.01" name="price"
                                   class="form-control"
                                   value="<?php echo $price; ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">รายละเอียด</label>
                            <textarea name="description" class="form-control"
                                      rows="4"><?php echo $description; ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">รูปภาพ</label>
                            <input type="file" name="image" class="form-control" accept="image/*">

                            <?php if($image): ?>
                                <div class="mt-2">
                                    <img src="../assets/images/<?php echo $image; ?>"
                                         width="100" class="rounded border">
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-grid">
                            <button type="submit" name="save_product"
                                    class="btn btn-success btn-lg">
                                บันทึกข้อมูล
                            </button>
                        </div>
                    </form>


                    <!-- ===========================
                         IMPORT CSV (เพิ่มใหม่)
                    ============================ -->
                    <hr class="my-4">

                    <h5>นำเข้าสินค้าจากไฟล์ CSV</h5>

                    <form action="" method="POST" enctype="multipart/form-data">

                        <div class="mb-3">
                            <label class="form-label">เลือกไฟล์ CSV</label>
                            <input type="file" name="csv_file"
                                   class="form-control"
                                   accept=".csv"
                                   required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" name="import_csv"
                                    class="btn btn-primary">
                                Import CSV
                            </button>
                        </div>
                    </form>


                    <!-- ===========================
                         ตัวอย่าง CSV
                    ============================ -->
                    <div class="mt-3 alert alert-info">
                        <b>รูปแบบ CSV:</b><br>
                        category,name,description,price<br>
                        ไรเฟิลจู่โจม,AK-47,ไรเฟิลยอดนิยม,35000<br>
                        ปืนพก,Glock 17,ปืนพกน้ำหนักเบา,18000
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
</body>
</html>
