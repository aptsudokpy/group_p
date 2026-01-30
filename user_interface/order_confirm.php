<?php 
require_once '../config/db.php'; 

// 1. ตรวจสอบว่าล็อกอินหรือยัง
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2. ตรวจสอบว่ามีของในตะกร้าไหม
if (empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$cart = $_SESSION['cart'];
$total_price = 0;

try {
    // เริ่ม Transaction (เพื่อให้แน่ใจว่าบันทึกครบทุกตาราง ถ้าพลาดให้ยกเลิกหมด)
    $conn->beginTransaction();

    // 3. คำนวณยอดเงินรวม (ดึงราคาจริงจาก DB เสมอ ป้องกันการโกงตัวเลขจากหน้าเว็บ)
    foreach ($cart as $p_id => $qty) {
        $stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
        $stmt->execute([$p_id]);
        $product = $stmt->fetch();
        if ($product) {
            $total_price += $product['price'] * $qty;
        }
    }

    // 4. บันทึกลงตาราง orders (หัวบิล)
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_price, status) VALUES (?, ?, 'pending')");
    $stmt->execute([$user_id, $total_price]);
    $order_id = $conn->lastInsertId(); // *สำคัญ* ดึง ID ของออเดอร์ที่เพิ่งสร้าง

    // 5. บันทึกลงตาราง order_details (รายการสินค้าในบิล)
    $stmt_detail = $conn->prepare("INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    
    foreach ($cart as $p_id => $qty) {
        // ดึงข้อมูลสินค้าอีกรอบเพื่อเอามาบันทึก
        $stmt_product = $conn->prepare("SELECT price FROM products WHERE id = ?");
        $stmt_product->execute([$p_id]);
        $product = $stmt_product->fetch();

        if ($product) {
            $stmt_detail->execute([$order_id, $p_id, $qty, $product['price']]);
        }
    }

    // 6. บันทึกสำเร็จ -> ล้างตะกร้า
    unset($_SESSION['cart']);
    
    // ยืนยัน Transaction
    $conn->commit();

} catch (Exception $e) {
    // ถ้ามี error ให้ยกเลิกการบันทึกทั้งหมด
    $conn->rollBack();
    echo "เกิดข้อผิดพลาด: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>สั่งซื้อสำเร็จ - Group P</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include '../includes/navbar.php'; ?>

    <div class="container mt-5 text-center">
        <div class="card shadow-sm p-5 border-0">
            <div class="card-body">
                <div class="mb-4">
                    <h1 class="display-1 text-success">✅</h1>
                </div>
                <h2 class="text-success fw-bold">สั่งซื้อสำเร็จ!</h2>
                <p class="lead text-muted">ขอบคุณที่ใช้บริการ หมายเลขคำสั่งซื้อของคุณคือ <strong>#<?php echo $order_id; ?></strong></p>
                <hr class="w-50 mx-auto my-4">
                
                <div class="d-flex justify-content-center gap-3">
                    <a href="order_history.php" class="btn btn-primary px-4">ดูประวัติการสั่งซื้อ</a>
                    <a href="index.php" class="btn btn-outline-secondary px-4">เลือกซื้อสินค้าต่อ</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>