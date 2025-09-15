<nav class="navbar navbar-expand-sm bg-dark navbar-dark no-print"
    style="background-image: linear-gradient(#eeaeca, #94bbe9);">
    <div class="container">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>admin/dashboard.php">Admin Panel</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>admin/dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="<?php echo BASE_URL; ?>authors/list.php">Authors</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" aria-current="page"
                        href="<?php echo BASE_URL; ?>publishers/list.php">Publishers</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>books/list.php">Manage Books</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>customers/">Manage Customers</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>orders/list.php">Manage Orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>orders/order_report.php">Order Reports</a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>auth/logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
