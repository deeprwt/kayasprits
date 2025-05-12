<aside class="main-sidebar elevation-4 sidebar-light-danger">

    <!-- Brand Logo

    <a href="dashboard.php" class="brand-link">

      <img src="dist/img/Logo.png" class="brand-image img-circle elevation-3" style="opacity: .8">

      <span class="brand-text font-weight-light">Mybusiinfo.com</span>

    </a>
 -->

    <!-- Sidebar -->

    <div class="sidebar">

      <!-- Sidebar user panel (optional) -->

      <div class="user-panel mt-3 pb-3 mb-3 d-flex">

        <div class="image">
           <i class="fas fa-users-cog"></i>

        </div>

        <div class="info">

          <a href="javascript:void" class="d-block">Admin</a>

        </div>

      </div>



      <!-- Sidebar Menu -->

      <nav class="mt-2">

        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

          

          <li class="nav-item">

            <a href="inquiry.php" class="nav-link">

              <i class="nav-icon fas fa-address-card"></i>

              <p>Kayaspirits Inquiry List</p>

            </a>

          </li>

          
          <?php if($_SESSION['admin_user']['type']==0){?>

              <li class="nav-item">

            <a href="users.php" class="nav-link">

              <i class="nav-icon fas fa-address-book"></i>

              <p>Users</p>

            </a>

          </li>

          <?php } ?>

          <li class="nav-item">

            <a href="logout.php" class="nav-link">

              <i class="nav-icon fas fa-sign-out-alt"></i>

              <p>Sign Out</p>

            </a>

          </li>

        </ul>

      </nav>

      <!-- /.sidebar-menu -->

    </div>

    <!-- /.sidebar -->

  </aside>

