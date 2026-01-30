<?php 
require_once '../config/db.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ดึงข้อความจาก Admin
$stmt = $conn->prepare("SELECT admin_message FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$msg = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ข้อความจากผู้ดูแล - Group P</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include '../includes/navbar.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow border-danger">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0">⚠️ ข้อความแจ้งเตือนจากผู้ดูแลระบบ</h4>
                    </div>
                    <div class="card-body p-5 text-center">
                        
                        <?php if(!empty($msg)): ?>
                            <div class="alert alert-warning text-dark text-start">
                                <h5 class="alert-heading fw-bold">เรียน ลูกค้าผู้มีอุปการคุณ</h5>
                                <hr>
                                <p class="fs-5 mb-0" style="white-space: pre-wrap;"><?php echo $msg; ?></p>
                            </div>
                            <p class="text-muted mt-4 small">
                                หากมีข้อสงสัย กรุณาติดต่อ Admin หรือปฏิบัติตามกฎการซื้ออาวุธอย่างเคร่งครัด
                            </p>
                        <?php else: ?>
                            <div class="py-5">
                                <h1 class="display-1 text-success">✅</h1>
                                <h3 class="mt-3">บัญชีของคุณปกติ</h3>
                                <p class="text-muted">ไม่พบรายงานความประพฤติ หรือ ข้อความแจ้งเตือนใดๆ</p>
                            </div>
                        <?php endif; ?>

                        <a href="index.php" class="btn btn-outline-secondary mt-3">กลับหน้าหลัก</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>