<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>


<header style="z-index:10;">
    <nav>
        <ul class="sidebar">
            <li onclick="hideSidebar()"><a href="basket"><svg xmlns="http://www.w3.org/2000/svg" height="26" viewBox="0 -960 960 960" width="26"><path d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z"/></svg></a></li>
            <li><a href="index.php">Home</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="shop.php">Shop</a></li>
            <?php if (isset($_SESSION['username'])): ?>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="logout.php">Logout</a></li>
                <li><a href="wishlist.php">My wishlist</a></li>

            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
            <?php endif; ?>
            <li class="cart-icon"><a href="#"><i class="fas fa-shopping-cart"></i></a></li>
        </ul>   
        <ul>
            <li><a class="logo" href="index.php"><img src="images/logo.png" alt="Collective Shop Sphere"></a></li>
            <li class="hideOnMobile"><a href="index.php">Home</a></li>
            <li class="hideOnMobile"><a href="about.php">About</a></li>
            <li class="hideOnMobile"><a href="shop.php">Shop</a></li>
            <?php if (isset($_SESSION['username'])): ?>
                <li class="hideOnMobile"><a href="dashboard.php"><?php echo htmlspecialchars($_SESSION['username']); ?>'s<br> Dashboard</a></li>
                <li class="hideOnMobile"><a href="logout.php">Logout</a></li>
                <li><a href="wishlist.php">My wishlist</a></li>
            <?php else: ?>
                <li class="hideOnMobile"><a href="login.php">Login</a></li>
                <li class="hideOnMobile"><a href="register.php">Register</a></li>
            <?php endif; ?>
            <li class="hideOnMobile cart-icon"><a href="basket"><i class="fas fa-shopping-cart"></i></a></li>
            <li class="menu-button" onclick="showSidebar()"><a href="#"><svg xmlns="http://www.w3.org/2000/svg" height="26" viewBox="0 -960 960 960" width="26"><path d="M120-240v-80h720v80H120Zm0-200v-80h720v80H120Zm0-200v-80h720v80H120Z"/></svg></a></li>
        </ul>
    </nav>  
</header>
