<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../interface.css">
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="nav-logo">
        <span class="logo-icon">OM</span>
        Online Marketplace
    </a>
    <ul class="nav-links">
        <li><span class="nav-badge seller-badge">Seller</span></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<div class="page-wrapper">

    <div class="welcome-banner">
        <div>
            <h1>Welcome back, <?php echo isset($_SESSION['uname']) ? htmlspecialchars($_SESSION['uname']) : 'Seller'; ?>!</h1>
            <p>Manage your products, address, and vouchers below.</p>
        </div>
        <span class="role-badge seller-badge">Seller Account</span>
    </div>

    <p class="section-title">What would you like to do?</p>

    <div class="action-grid">

        <div class="action-card seller-card">
            <div class="card-icon icon-teal">&#127968;</div>
            <h3>Add Address</h3>
            <p>Register your store or shipping address.</p>
            <button class="card-btn" onclick="location.href='add-address.php'">Go to Add Address</button>
        </div>

        <div class="action-card seller-card">
            <div class="card-icon icon-green">&#128230;</div>
            <h3>Add Product</h3>
            <p>List a new product for sale on the marketplace.</p>
            <button class="card-btn" onclick="location.href='add-product.php'">Go to Add Product</button>
        </div>

        <div class="action-card seller-card">
            <div class="card-icon icon-amber">&#127987;</div>
            <h3>Add Voucher</h3>
            <p>Create discount vouchers for your customers.</p>
            <button class="card-btn" onclick="location.href='add-voucher.php'">Go to Add Voucher</button>
        </div>

    </div>

    <a href="logout.php" class="logout-link">&larr; Logout</a>

</div>

<div class="page-footer">
    &copy; <?php echo date('Y'); ?> Online Marketplace &mdash; Seller Portal
</div>

</body>
</html>
