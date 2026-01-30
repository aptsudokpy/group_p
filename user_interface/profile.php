<?php 
require_once '../config/db.php'; 

// 1. เช็คว่าล็อกอินยัง
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = ""; // ตัวแปรเก็บข้อความแจ้งเตือน

// 2. ถ้ามีการกดปุ่ม "บันทึกข้อมูลทั่วไป"
if (isset($_POST['update_profile'])) {
    // รับค่าจากฟอร์มครบทุกช่อง
    $fullname = $_POST['fullname'];
    $address = $_POST['address'];
    $email = $_POST['email'];   // <-- รับค่า email
    $phone = $_POST['phone'];   // <-- รับค่า phone

    try {
        // อัปเดต SQL: เพิ่ม email และ phone เข้าไปในคำสั่ง UPDATE
        $sql = "UPDATE users SET fullname = ?, address = ?, email = ?, phone = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        
        // เรียงลำดับตัวแปรให้ตรงกับเครื่องหมาย ? ด้านบน (fullname, address, email, phone, id)
        if ($stmt->execute([$fullname, $address, $email, $phone, $user_id])) {
            
            // อัปเดต Session ด้วยเพื่อให้ชื่อมุมบนขวาเปลี่ยนทันที
            $_SESSION['fullname'] = $fullname;
            $msg = "<div class='alert alert-success'>✅ บันทึกข้อมูลสำเร็จ</div>";
        } else {
            $msg = "<div class='alert alert-danger'>❌ เกิดข้อผิดพลาด ไม่สามารถบันทึกได้</div>";
        }
    } catch(PDOException $e) {
        $msg = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}

// 3. ถ้ามีการกดปุ่ม "เปลี่ยนรหัสผ่าน" (Optional)
if (isset($_POST['change_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (!empty($new_password) && ($new_password === $confirm_password)) {
        $hash_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmt->execute([$hash_password, $user_id])) {
            $msg = "<div class='alert alert-success'>✅ เปลี่ยนรหัสผ่านสำเร็จ</div>";
        }
    } else {
        $msg = "<div class='alert alert-danger'>❌ รหัสผ่านไม่ตรงกัน หรือ เป็นค่าว่าง</div>";
    }
}

// 4. ดึงข้อมูลล่าสุดมาแสดง (ดึงมาทั้งหมดรวมถึง email และ phone)
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ข้อมูลส่วนตัว - Group P</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <?php include '../includes/navbar.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            
            <div class="col-md-10 mb-3">
                <?php echo $msg; ?>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0 text-center p-4">
                    <div class="mb-3">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['fullname']); ?>&background=random&size=128" 
                             class="rounded-circle shadow-sm" alt="Profile">
                    </div>
                    <h5 class="fw-bold"><?php echo $user['fullname']; ?></h5>
                    <p class="text-muted mb-1">@<?php echo $user['username']; ?></p>
                    <span class="badge bg-primary rounded-pill"><?php echo strtoupper($user['role']); ?></span>
                    
                    <hr>
                    <div class="text-start">
                        <small class="text-muted">วันที่สมัครสมาชิก</small>
                        <p class="mb-0"><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-primary fw-bold">✏️ แก้ไขข้อมูลส่วนตัว</h5>
                    </div>
                    <div class="card-body p-4">
                
                    <form action="profile.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">ชื่อ-นามสกุล</label>
                            <input type="text" name="fullname" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['fullname'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">ที่อยู่จัดส่ง (สำหรับส่งสินค้า)</label>
                            <textarea name="address" class="form-control" rows="3" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-5">
                            <label class="form-label">เบอร์โทร</label>
                            <input type="text" name="phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                        </div>

                        <div class="text-end">
                            <button type="submit" name="update_profile" class="btn btn-success px-4">บันทึกข้อมูล</button>
                        </div>
                    </form>

                        <hr class="my-4">

                        <h6 class="text-danger mb-3">🔒 เปลี่ยนรหัสผ่าน</h6>
                        <form action="profile.php" method="POST">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <input type="password" name="new_password" class="form-control" placeholder="รหัสผ่านใหม่">
                                </div>
                                <div class="col-md-6">
                                    <input type="password" name="confirm_password" class="form-control" placeholder="ยืนยันรหัสผ่านใหม่">
                                </div>
                            </div>
                            <div class="text-end mt-3">
                                <button type="submit" name="change_password" class="btn btn-outline-danger btn-sm">ยืนยันเปลี่ยนรหัส</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>