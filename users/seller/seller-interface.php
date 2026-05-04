<?php
    session_start();
    if (!isset($_SESSION['userId'])) {
        header("Location: ../../login.php");
        exit();
    }

    $con = mysqli_connect("127.0.0.1", "root", "", "online_marketplace") or die("Error in connection.");
    $userId = $_SESSION['userId'];

    $products = mysqli_query($con, "SELECT p.*, c.CategoryName FROM product p LEFT JOIN category c ON p.CategoryID = c.CategoryID WHERE p.SellerID = $userId");
    $vouchers = mysqli_query($con, "SELECT VoucherID, Code, DiscountAmount, ExpirationDay, ExpirationMonth, ExpirationYear, UsageLimit FROM Voucher");
?>

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
            <li><a href="../logout.php">Logout</a></li>
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
                <h3>Edit Profile</h3>
                <p>Customize your user profile.</p>
                <button class="card-btn" onclick="location.href='../edit-profile.php'">Go to Edit Profile</button>
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

        <a href="../logout.php" class="logout-link">&larr; Logout</a>

    </div>

    <p class="section-title" style="justify-self: center; width: 50vw;">Your Products</p>
    <table class="records-table">
        <thead>
            <tr><th>Product Name</th><th>Price</th><th>Stock</th><th>Category</th></tr>
        </thead>
        <tbody>
            <?php if(mysqli_num_rows($products) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($products)): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($row['ProductName']); ?></strong></td>
                        <td>$<?php echo number_format($row['Price'], 2); ?></td>
                        <td><?php echo $row['StockQuantity']; ?></td>
                        <td><?php echo htmlspecialchars($row['CategoryName'] ?? 'Uncategorized'); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4" style="text-align:center; color:#888;">No products listed yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

   <p class="section-title" style="justify-self: center; width: 50vw;">Vouchers</p>
    <table class="records-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Promo Code</th>
                <th>Discount</th>
                <th>Expiration (D/M/Y)</th>
                <th>Limit</th>
            </tr>
        </thead>
        <tbody>
            <?php if(mysqli_num_rows($vouchers) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($vouchers)): ?>
                    <tr>
                        <td><?php echo $row['VoucherID']; ?></td>
                        <td style="font-weight: bold; color: #0a4cd3;"><?php echo htmlspecialchars($row['Code']); ?></td>
                        <td>$<?php echo number_format($row['DiscountAmount'], 2); ?></td>
                        <td><?php echo $row['ExpirationDay']."/".$row['ExpirationMonth']."/".$row['ExpirationYear']; ?></td>
                        <td><?php echo $row['UsageLimit']; ?> times</td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" style="text-align:center; color:#888;">No active vouchers found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="page-footer">
        &copy; <?php echo date('Y'); ?> Online Marketplace &mdash; Seller Portal
    </div>

</body>
</html>

<style>
    .records-table { 
            justify-self: center;
            width: 50vw; 
            border-collapse: collapse; 
            margin-top: 15px; 
            margin-bottom: 30px; 
            background: white; 
            border-radius: 12px; 
            overflow: hidden; 
            box-shadow: 0 4px 16px rgba(0,0,0,0.05);
        }
        .records-table th, .records-table td { 
            padding: 14px; 
            border-bottom: 1px solid #e6e9ef; 
            text-align: left; 
            font-size: 14px; 
        }
        .records-table th { 
            background: #f2f4f8; 
            color: #888; 
            font-size: 11px; 
            text-transform: uppercase; 
            letter-spacing: 0.05em; 
        }
        .records-table tr:last-child td { border-bottom: none; }
</style>
