<nav class="navbar navbar-expand-lg navbar-dark sticky-top" style="background: linear-gradient(135deg, #050a14, #0d1626); border-bottom: 2px solid #00f2ff; box-shadow: 0 4px 20px rgba(0, 242, 255, 0.1);">
  <div class="container">

    <!-- Logo -->
    <a class="navbar-brand fw-bold" href="index.php" style="font-family: 'Orbitron', sans-serif; letter-spacing: 2px; font-size: 18px;">
      <i class="fas fa-lock" style="color: #00f2ff; margin-right: 8px;"></i><span style="background: linear-gradient(135deg, #00f2ff, #ff3333); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">AMMO</span>
    </a>

    <!-- Toggle Button -->
    <button class="navbar-toggler border-0 shadow-none" 
            type="button" 
            data-bs-toggle="collapse" 
            data-bs-target="#navbarNav"
            style="color: #00f2ff;">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Menu -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">

        <!-- หน้าแรก -->
        <li class="nav-item">
          <a class="nav-link px-3" href="index.php" style="color: #00f2ff; transition: 0.3s; text-transform: uppercase; font-size: 12px; letter-spacing: 1px; font-weight: 600;">
            <i class="fas fa-home me-2"></i>HOME
          </a>
        </li>

        <?php if(isset($_SESSION['user_id'])): ?>

          <!-- ตะกร้า -->
          <li class="nav-item">
            <a class="nav-link px-3" href="cart.php" style="color: #00f2ff; transition: 0.3s; text-transform: uppercase; font-size: 12px; letter-spacing: 1px; font-weight: 600;">
              <i class="fas fa-shopping-cart me-2"></i>CART
            </a>
          </li>

          <!-- Dropdown User -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle px-3"
               href="#"
               id="navbarDropdown"
               role="button"
               data-bs-toggle="dropdown"
               aria-expanded="false"
               style="color: #00f2ff; transition: 0.3s; text-transform: uppercase; font-size: 12px; letter-spacing: 1px; font-weight: 600;">
               <i class="fas fa-user-circle me-2"></i><?php echo htmlspecialchars($_SESSION['username']); ?>
            </a>

            <ul class="dropdown-menu dropdown-menu-end" style="background: #0d1626; border: 1px solid #00f2ff; border-radius: 4px;">

              <li>
                <a class="dropdown-item" href="profile.php" style="color: #00f2ff; text-transform: uppercase; font-size: 11px; letter-spacing: 1px;">
                  <i class="fas fa-user me-2"></i>PROFILE
                </a>
              </li>

              <li>
                <a class="dropdown-item" href="order_history.php" style="color: #00f2ff; text-transform: uppercase; font-size: 11px; letter-spacing: 1px;">
                  <i class="fas fa-history me-2"></i>ORDERS
                </a>
              </li>

              <li>
                <a class="dropdown-item" href="admin_message.php" style="color: #ff3333; text-transform: uppercase; font-size: 11px; letter-spacing: 1px;">
                  <i class="fas fa-bell me-2"></i>MESSAGES
                </a>
              </li>

              <li><hr class="dropdown-divider" style="border-color: #00f2ff;"></li>

              <li>
                <a class="dropdown-item" href="logout.php" style="color: #ff3333; text-transform: uppercase; font-size: 11px; letter-spacing: 1px;">
                  <i class="fas fa-sign-out-alt me-2"></i>LOGOUT
                </a>
              </li>

            </ul>
          </li>

        <?php else: ?>

          <!-- Login -->
          <li class="nav-item">
            <a class="nav-link btn ms-lg-3 px-3"
               href="login.php"
               style="background: linear-gradient(135deg, #ff3333, #ff5555); color: #fff; border: 2px solid #ff3333; text-transform: uppercase; font-size: 11px; letter-spacing: 1px; font-weight: 600; border-radius: 4px; transition: 0.3s;">
               <i class="fas fa-sign-in-alt me-2"></i>LOGIN
            </a>
          </li>

        <?php endif; ?>

      </ul>
    </div>
  </div>

  <style>
    .navbar .navbar-brand:hover {
      text-shadow: 0 0 15px rgba(0, 242, 255, 0.5);
    }
    
    .navbar .nav-link:hover {
      color: #ff3333 !important;
      text-shadow: 0 0 10px rgba(255, 51, 51, 0.5);
    }
    
    .navbar .dropdown-item:hover {
      background: rgba(0, 242, 255, 0.1);
    }
    
    .navbar .btn-link:hover {
      box-shadow: 0 0 15px rgba(255, 51, 51, 0.3);
    }
  </style>
</nav>
