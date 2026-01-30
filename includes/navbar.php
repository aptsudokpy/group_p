<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">🛍️ Group P Shop</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">หน้าแรก</a></li>
        
        <?php if(isset($_SESSION['user_id'])): ?>
            <li class="nav-item"><a class="nav-link" href="cart.php">ตะกร้าสินค้า 🛒</a></li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                    สวัสดี, <?php echo $_SESSION['username']; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li><a class="dropdown-item" href="profile.php">👤 ข้อมูลส่วนตัว</a></li>
                  <li><a class="dropdown-item" href="order_history.php">📜 ประวัติการสั่งซื้อ</a></li>
                  
                  <li><a class="dropdown-item text-danger" href="admin_message.php">⚠️ ข้อความจากผู้ดูแล</a></li>
                  
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item text-danger" href="logout.php">ออกจากระบบ</a></li>
              </ul>
            </li>
        <?php else: ?>
            <li class="nav-item"><a class="nav-link btn btn-outline-light ms-2" href="login.php">เข้าสู่ระบบ</a></li>
            <li class="nav-item"><a class="nav-link btn btn-warning text-dark ms-2" href="register.php">สมัครสมาชิก</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>