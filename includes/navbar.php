<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
  <div class="container">

    <!-- Logo -->
    <a class="navbar-brand fw-bold" href="index.php">
      🛍️ ProjectWeb_e-commerce
    </a>

    <!-- Toggle Button -->
    <button class="navbar-toggler border-0 shadow-none" 
            type="button" 
            data-bs-toggle="collapse" 
            data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Menu -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">

        <!-- หน้าแรก -->
        <li class="nav-item">
          <a class="nav-link px-3" href="index.php">
            🏠 หน้าแรก
          </a>
        </li>

        <?php if(isset($_SESSION['user_id'])): ?>

          <!-- ตะกร้า -->
          <li class="nav-item">
            <a class="nav-link px-3" href="cart.php">
              🛒 ตะกร้าสินค้า
            </a>
          </li>

          <!-- Dropdown User -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle px-3"
               href="#"
               id="navbarDropdown"
               role="button"
               data-bs-toggle="dropdown"
               aria-expanded="false">
               👋 สวัสดี, <?php echo htmlspecialchars($_SESSION['username']); ?>
            </a>

            <ul class="dropdown-menu dropdown-menu-end">

              <li>
                <a class="dropdown-item" href="profile.php">
                  👤 ข้อมูลส่วนตัว
                </a>
              </li>

              <li>
                <a class="dropdown-item" href="order_history.php">
                  📜 ประวัติการสั่งซื้อ
                </a>
              </li>

              <li>
                <a class="dropdown-item text-danger" href="admin_message.php">
                  ⚠️ ข้อความจากผู้ดูแล
                </a>
              </li>

              <li><hr class="dropdown-divider"></li>

              <li>
                <a class="dropdown-item text-danger" href="logout.php">
                  🚪 ออกจากระบบ
                </a>
              </li>

            </ul>
          </li>

        <?php else: ?>

          <!-- Login -->
          <li class="nav-item">
            <a class="nav-link btn btn-outline-light ms-lg-3 px-3"
               href="login.php">
               🔐 เข้าสู่ระบบ
            </a>
          </li>

        <?php endif; ?>

      </ul>
    </div>
  </div>
</nav>
