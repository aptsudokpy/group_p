<?php 
require_once '../config/db.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../user_interface/login.php");
    exit();
}

// ตัวแปรเริ่มต้น
$id = "";
$name = "";
$price = "";
$description = "";
$image = "";
$category_id = ""; // *เพิ่มตัวแปรเก็บหมวดหมู่
$title_page = "เพิ่มสินค้าใหม่";

// --- โหมดแก้ไข: ดึงข้อมูลเก่า ---
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
        $category_id = $product['category_id']; // *ดึงหมวดหมู่เดิมมาด้วย
    }
}

// --- ดึงรายชื่อหมวดหมู่ทั้งหมดมาใส่ใน Dropdown ---
$cat_stmt = $conn->query("SELECT * FROM categories");
$categories = $cat_stmt->fetchAll();

// --- บันทึกข้อมูล ---
if (isset($_POST['save_product'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $cat_id = $_POST['category_id']; // *รับค่าหมวดหมู่
    $id = $_POST['id'];
    $old_image = $_POST['old_image'];
    $new_filename = $old_image;

    // อัปโหลดรูป
    if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != "") {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $new_filename = time() . "_" . uniqid() . "." . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], "../assets/images/" . $new_filename);
    }

    if ($id != "") {
        // UPDATE (เพิ่ม category_id)
        $sql = "UPDATE products SET name=?, price=?, description=?, image=?, category_id=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([$name, $price, $description, $new_filename, $cat_id, $id]);
    } else {
        // INSERT (เพิ่ม category_id)
        $sql = "INSERT INTO products (name, price, description, image, category_id) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([$name, $price, $description, $new_filename, $cat_id]);
    }

    if ($result) {
        header("Location: manage_products.php");
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
                        
                        <form action="product_form.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <input type="hidden" name="old_image" value="<?php echo $image; ?>">

                            <div class="mb-3">
                                <label class="form-label">ชื่อสินค้า</label>
                                <input type="text" name="name" class="form-control" value="<?php echo $name; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">หมวดหมู่สินค้า</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">-- กรุณาเลือกหมวดหมู่ --</option>
                                    <?php foreach($categories as $c): ?>
                                        <option value="<?php echo $c['id']; ?>" <?php if($category_id == $c['id']) echo 'selected'; ?>>
                                            <?php echo $c['name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">ราคา (บาท)</label>
                                <input type="number" step="0.01" name="price" class="form-control" value="<?php echo $price; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">รายละเอียด</label>
                                <textarea name="description" class="form-control" rows="4"><?php echo $description; ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">รูปภาพ</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                                <?php if($image): ?>
                                    <div class="mt-2">
                                        <img src="../assets/images/<?php echo $image; ?>" width="100" class="rounded border">
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="d-grid">
                                <button type="submit" name="save_product" class="btn btn-success btn-lg">บันทึกข้อมูล</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>