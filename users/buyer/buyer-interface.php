<?php
    session_start();
    if (!isset($_SESSION['userId'])) {
        header("Location: /OnlineMarketplace/login.php");
        exit();
    }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../interface.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <a href="index.php" class="nav-logo">
        <span class="logo-icon">OM</span>
        Online Marketplace
    </a>
    <ul class="nav-links">
        <li><span class="nav-badge buyer-badge">Buyer</span></li>
        <li><a href="../logout.php">Logout</a></li>
    </ul>
</nav>

<!-- Page Content -->
<div class="page-wrapper">

    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div>
            <h1>Welcome back, <?php echo isset($_SESSION['uname']) ? htmlspecialchars($_SESSION['uname']) : 'Buyer'; ?>!</h1>
            <p>Browse, shop, and manage your orders below.</p>
        </div>
        <span class="role-badge buyer-badge">Buyer Account</span>
    </div>

    <!-- Actions -->
    <p class="section-title">What would you like to do?</p>

    <div class="action-grid">

        <!-- Edit Profile -->
        <div class="action-card">
            <div class="card-icon icon-teal">&#127968;</div>
            <h3>Edit Profile</h3>
            <p>Customize your user profile. <br><br></p>
            <button class="card-btn" onclick="location.href='../edit-profile.php'">Go to Edit Profile</button>
        </div>


        <!-- Add to Cart / Order Item -->
        <div class="action-card">
            <div class="card-icon icon-blue">&#128722;</div>
            <h3>View Marketplace</h3>
            <p>Browse products and add items to your cart.</p>
            <button class="card-btn" onclick="location.href='market.php'">Go to Marketplace</button>
        </div>

        <!-- Add Order / Checkout -->
        <div class="action-card">
            <div class="card-icon icon-green">&#9989;</div>
            <h3>Checkout</h3>
            <p>Review your cart and place your order.</p>
            <button class="card-btn" onclick="location.href='checkout.php'">Go to Checkout</button>
        </div>

        <!-- Add Payment -->
        <div class="action-card">
            <div class="card-icon icon-purple">&#128179;</div>
            <h3>Add Payment</h3>
            <p>Complete payment for your pending order.</p>
            <button class="card-btn" onclick="location.href='add-payment.php'">Go to Payment</button>
        </div>

    </div>

    <a href="../logout.php" class="logout-link">&larr; Logout</a>

</div>

<div class="page-footer">
    &copy; <?php echo date('Y'); ?> Online Marketplace &mdash; Buyer Portal
</div>

</body>
</html>