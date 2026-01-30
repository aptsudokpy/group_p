<?php 
require_once '../config/db.php'; 

// ตรวจสอบว่ามี ID ส่งมาไหม
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

// ถ้าหาไม่เจอให้กลับหน้าแรก
if (!$product) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> - Group P</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <?php include '../includes/navbar.php'; ?>

    <div class="container mt-5">
        <div class="card shadow-sm border-0">
            <div class="row g-0">
                <div class="col-md-5">
                    <img src="../assets/images/<?php echo $product['image'] ? $product['image'] : 'no-image.jpg'; ?>" 
                         class="img-fluid rounded-start w-100 h-100 object-fit-cover" 
                         style="min-height: 400px;" 
                         alt="<?php echo $product['name']; ?>">
                </div>
                <div class="col-md-7">
                    <div class="card-body p-5">
                        <h2 class="card-title fw-bold mb-3"><?php echo $product['name']; ?></h2>
                        <h3 class="text-primary mb-4">฿<?php echo number_format($product['price'], 2); ?></h3>
                        
                        <p class="card-text text-muted mb-5" style="line-height: 1.8;">
                            <?php echo nl2br($product['description']); ?>
                        </p>

                        <hr class="my-4">

                        <form action="cart.php" method="POST" class="d-flex align-items-end gap-3">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <input type="hidden" name="action" value="add">
                            
                            <div style="width: 100px;">
                                <label class="form-label">จำนวน</label>
                                <input type="number" name="quantity" class="form-control" value="1" min="1">
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg px-4 flex-grow-1">
                                🛒 หยิบลงตะกร้า
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <a href="index.php" class="btn btn-outline-secondary">← กลับไปเลือกสินค้า</a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>