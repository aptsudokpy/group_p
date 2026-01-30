<?php 
require_once '../config/db.php'; 

// --- ส่วน Logic จัดการตะกร้า ---

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// 1. เพิ่มสินค้าใหม่ (จากหน้า Product Detail)
if (isset($_POST['action']) && $_POST['action'] == 'add') {
    $pid = $_POST['product_id'];
    $qty = $_POST['quantity'];

    if (isset($_SESSION['cart'][$pid])) {
        $_SESSION['cart'][$pid] += $qty;
    } else {
        $_SESSION['cart'][$pid] = $qty;
    }
    header("Location: cart.php");
    exit();
}

// 2. อัปเดตจำนวนสินค้า (+ หรือ -) *** ส่วนที่เพิ่มมาใหม่ ***
if (isset($_GET['action']) && $_GET['action'] == 'update_qty') {
    $pid = $_GET['id'];
    $mode = $_GET['mode']; // รับค่าว่าเป็น 'increase' หรือ 'decrease'

    if (isset($_SESSION['cart'][$pid])) {
        if ($mode == 'increase') {
            $_SESSION['cart'][$pid]++; // บวกเพิ่ม 1
        } elseif ($mode == 'decrease') {
            $_SESSION['cart'][$pid]--; // ลบออก 1
            if ($_SESSION['cart'][$pid] <= 0) {
                unset($_SESSION['cart'][$pid]); // ถ้าเหลือ 0 ให้ลบทิ้งเลย
            }
        }
    }
    header("Location: cart.php");
    exit();
}

// 3. ลบสินค้า (ปุ่มกากบาท)
if (isset($_GET['action']) && $_GET['action'] == 'remove') {
    $pid = $_GET['id'];
    unset($_SESSION['cart'][$pid]);
    header("Location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ตะกร้าสินค้า - Group P</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <?php include '../includes/navbar.php'; ?>

    <div class="container mt-5">
        <h2 class="mb-4">🛒 ตะกร้าสินค้าของคุณ</h2>

        <?php if (empty($_SESSION['cart'])): ?>
            <div class="alert alert-info text-center p-5">
                <h4>ยังไม่มีสินค้าในตะกร้า</h4>
                <a href="index.php" class="btn btn-primary mt-3">ไปเลือกซื้อสินค้า</a>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>สินค้า</th>
                                        <th>ราคา</th>
                                        <th style="width: 150px;">จำนวน</th> <th>รวม</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $total_order = 0;
                                    foreach ($_SESSION['cart'] as $p_id => $qty): 
                                        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
                                        $stmt->execute([$p_id]);
                                        $product = $stmt->fetch();
                                        
                                        if($product):
                                            $subtotal = $product['price'] * $qty;
                                            $total_order += $subtotal;
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="../assets/images/<?php echo !empty($product['image']) ? $product['image'] : 'no-image.jpg'; ?>" 
                                                     width="50" height="50" class="me-3 rounded object-fit-cover">
                                                <div>
                                                    <h6 class="mb-0"><?php echo $product['name']; ?></h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>฿<?php echo number_format($product['price'], 2); ?></td>
                                        
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <a href="cart.php?action=update_qty&id=<?php echo $p_id; ?>&mode=decrease" class="btn btn-outline-secondary">-</a>
                                                <input type="text" class="form-control text-center bg-white" value="<?php echo $qty; ?>" readonly>
                                                <a href="cart.php?action=update_qty&id=<?php echo $p_id; ?>&mode=increase" class="btn btn-outline-secondary">+</a>
                                            </div>
                                        </td>
                                        
                                        <td class="fw-bold">฿<?php echo number_format($subtotal, 2); ?></td>
                                        <td>
                                            <a href="cart.php?action=remove&id=<?php echo $p_id; ?>" class="text-danger" onclick="return confirm('ลบสินค้านี้?');">
                                                ❌ </a>
                                        </td>
                                    </tr>
                                    <?php endif; endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">สรุปคำสั่งซื้อ</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <span>ยอดรวมสินค้า</span>
                                <strong>฿<?php echo number_format($total_order, 2); ?></strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-4">
                                <span class="h5">ยอดสุทธิ</span>
                                <span class="h5 text-primary">฿<?php echo number_format($total_order, 2); ?></span>
                            </div>

                            <?php if(isset($_SESSION['user_id'])): ?>
                                <a href="order_confirm.php" class="btn btn-success w-100 py-2">ดำเนินการสั่งซื้อ</a>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-warning w-100 py-2">เข้าสู่ระบบเพื่อสั่งซื้อ</a>
                            <?php endif; ?>
                            
                            <a href="index.php" class="btn btn-outline-secondary w-100 py-2 mt-2">ซื้อสินค้าต่อ</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>