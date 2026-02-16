<?php 
ob_start(); // ป้องกัน Error: Cannot modify header information
require_once '../config/db.php'; 

// 1. เช็ค Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = ""; 

// 2. อัปเดตข้อมูลทั่วไป (แก้ไขให้ Redirect เพื่อลบหน้าต่างแจ้งเตือนรีเฟรช)
if (isset($_POST['update_profile'])) {
    $fullname = $_POST['fullname'];
    $address = $_POST['address'];
    $email = $_POST['email'];  
    $phone = $_POST['phone'];  

    try {
        $sql = "UPDATE users SET fullname = ?, address = ?, email = ?, phone = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$fullname, $address, $email, $phone, $user_id])) {
            $_SESSION['fullname'] = $fullname;
            // หลังจากบันทึกเสร็จ ให้ Redirect กลับมาที่หน้าเดิมพร้อมสถานะ
            header("Location: profile.php?status=update_success");
            exit();
        }
    } catch(PDOException $e) {
        $msg = "<div class='alert alert-danger bg-danger text-white border-0'>Error: " . $e->getMessage() . "</div>";
    }
}

// 3. เปลี่ยนรหัสผ่าน (แก้ไขให้ Redirect)
if (isset($_POST['change_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (!empty($new_password) && ($new_password === $confirm_password)) {
        $hash_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmt->execute([$hash_password, $user_id])) {
            header("Location: profile.php?status=password_success");
            exit();
        }
    } else {
        $msg = "<div class='alert alert-danger bg-danger text-white border-0'>❌ รหัสผ่านไม่ตรงกัน</div>";
    }
}

// รับค่าสถานะเพื่อแสดงข้อความหลังจาก Redirect
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'update_success') {
        $msg = "<div class='alert alert-success bg-success text-white border-0'>✅ บันทึกข้อมูลยุทธการสำเร็จ</div>";
    } elseif ($_GET['status'] == 'password_success') {
        $msg = "<div class='alert alert-success bg-success text-white border-0'>✅ เปลี่ยนรหัสผ่านเข้ารหัสใหม่สำเร็จ</div>";
    }
}

// 4. ดึงข้อมูลล่าสุด
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PROFILE - Group P Command Center</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-dark: #0a1120;
            --card-navy: #111b2d; /* สีทึบตามสั่ง */
            --accent-blue: #3b82f6;
            --text-white: #ffffff;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-white);
            font-family: 'Kanit', sans-serif;
            min-height: 100vh;
        }

        .font-tech { font-family: 'Orbitron', sans-serif; letter-spacing: 1px; }

        /* ปรับ Navbar ให้ทึบ */
        .navbar {
            background-color: var(--card-navy) !important;
            border-bottom: 1px solid rgba(59, 130, 246, 0.2);
        }

        /* การ์ดสไตล์ทึบแน่น */
        .profile-card {
            background: var(--card-navy);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 20px;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.6);
        }

        .avatar-container img {
            border: 4px solid var(--accent-blue);
            box-shadow: 0 0 20px var(--accent-blue);
            padding: 5px;
            background: var(--bg-dark);
        }

        /* ฟอร์มและตัวหนังสือ */
        .form-label {
            color: var(--text-white) !important; /* เปลี่ยนเป็นสีขาวตามสั่ง */
            font-weight: 500;
            margin-bottom: 8px;
            text-transform: uppercase;
            font-size: 0.85rem;
            font-family: 'Orbitron', sans-serif;
        }

        .form-control {
            background: #0f172a !important; /* พื้นหลังช่องกรอกสีเข้มทึบ */
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: #ffffff !important; /* ตัวหนังสือสีขาว */
            border-radius: 10px;
            padding: 12px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: #1e293b !important;
            border-color: var(--accent-blue);
            box-shadow: 0 0 12px rgba(59, 130, 246, 0.5);
            color: #ffffff !important;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.4) !important;
            font-style: italic;
        }

        /* ปุ่มจาก Uiverse.io */
        .btn-uiverse {
            padding: 1.1em 2.8em;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 2.5px;
            font-weight: 600;
            color: #ffffff !important;
            background-color: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 45px;
            box-shadow: 0px 8px 15px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            cursor: pointer;
            font-family: 'Orbitron', sans-serif;
        }

        .btn-uiverse:hover {
            background-color: #23c483;
            box-shadow: 0px 15px 20px rgba(46, 229, 157, 0.4);
            color: #ffffff !important;
            transform: translateY(-7px);
        }

        .btn-uiverse:active { transform: translateY(-1px); }

        .btn-save {
            background: var(--accent-blue);
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: 50px;
            font-family: 'Orbitron', sans-serif;
            transition: 0.3s;
        }

        .btn-save:hover {
            box-shadow: 0 0 15px var(--accent-blue);
            transform: translateY(-2px);
        }

        hr { border-color: rgba(59, 130, 246, 0.2); }
    </style>
</head>
<body>

    <?php include '../includes/navbar.php'; ?>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            
            <div class="col-md-10 mb-3">
                <?php echo $msg; ?>
            </div>

            <div class="col-md-4 mb-4">
                <div class="profile-card p-4 text-center">
                    <div class="avatar-container mb-3">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['fullname']); ?>&background=3b82f6&color=fff&size=128" 
                             class="rounded-circle" alt="Profile">
                    </div>
                    <h4 class="fw-bold text-white"><?php echo $user['fullname']; ?></h4>
                    <p class="font-tech text-info mb-2">@<?php echo $user['username']; ?></p>
                    <span class="badge bg-primary px-3 py-2 rounded-pill font-tech"><?php echo strtoupper($user['role']); ?></span>
                    
                    <hr>
                    <div class="text-start px-2 text-white">
                        <small class="font-tech text-info" style="font-size: 0.7rem;">JOINED DATE</small>
                        <p class="mb-0"><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></p>
                        
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <hr>
                            <div class="d-grid">
                                <a href="../admin/index.php" class="btn btn-outline-danger font-tech btn-sm">
                                    <i class="fas fa-user-shield me-2"></i>ADMIN PANEL
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="profile-card overflow-hidden">
                    <div class="card-header bg-black bg-opacity-50 border-bottom border-primary border-opacity-25 py-3 px-4">
                        <h5 class="mb-0 text-white fw-bold font-tech"><i class="fas fa-edit me-2 text-primary"></i>EDIT PROFILE DATA</h5>
                    </div>
                    <div class="card-body p-4">
                
                      <form action="profile.php" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name / ชื่อ-นามสกุล</label>
                                <input type="text" name="fullname" class="form-control" 
                                    placeholder="กรอกชื่อ-นามสกุล"
                                    value="<?php echo htmlspecialchars($user['fullname'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email / อีเมลติดต่อ</label>
                                <input type="email" name="email" class="form-control" 
                                    placeholder="example@mail.com"
                                    value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone / เบอร์โทรศัพท์</label>
                            <input type="text" name="phone" class="form-control" 
                                placeholder="08X-XXX-XXXX"
                                value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Shipping Address / ที่อยู่จัดส่ง</label>
                            <textarea name="address" class="form-control" rows="3" 
                                    placeholder="กรอกที่อยู่สำหรับจัดส่ง..."><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>

                        <div class="text-end">
                            <button type="submit" name="update_profile" class="btn-save px-4">
                                SAVE CHANGES
                            </button>
                        </div>
                    </form>

                        <hr class="my-4">

                        <h6 class="text-danger mb-3 font-tech">
                            <i class="fas fa-key me-2"></i>SECURITY / เปลี่ยนรหัสผ่าน
                        </h6>
                        <form action="profile.php" method="POST">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <input type="password" name="new_password" 
                                        class="form-control" 
                                        placeholder="รหัสผ่านใหม่ (NEW PASSWORD)">
                                </div>
                                <div class="col-md-6">
                                    <input type="password" name="confirm_password" 
                                        class="form-control" 
                                        placeholder="ยืนยันรหัสผ่านใหม่ (CONFIRM)">
                                </div>
                            </div>
                            <div class="text-end mt-4">
                                <button type="submit" name="change_password" class="btn-uiverse">
                                    Confirm New Password
                                </button>
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