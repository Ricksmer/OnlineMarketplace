<?php
    session_start();

    if (!isset($_SESSION['userId'])) {
        header("Location: ../../login.php");
        exit();
    }

    if($_SESSION['role'] !== 'seller'){
        header("Location: ../buyer/buyer-interface.php");
        exit();
    }

    $con = mysqli_connect("127.0.0.1", "root", "", "online_marketplace") or die("Error in connection.");
    $userId = $_SESSION['userId'];

    $products = mysqli_query($con, "
        SELECT p.*, c.CategoryName 
        FROM Product p 
        LEFT JOIN Category c ON p.CategoryID = c.CategoryID 
        WHERE p.SellerID = $userId
    ");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="../interface.css">
</head>

<body>

    <nav class="navbar">
        <a href="../../index.html" class="nav-logo">
            <span class="logo-icon">OM</span>
            Online Marketplace
        </a>

        <ul class="nav-links">
            <li><span class="nav-badge seller-badge">Seller</span></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="page-wrapper">
        <a href="seller-interface.php" class="back-link">&larr; Back</a>
    </div>

    <p class="section-title table-title">Your Products</p>

    <table class="records-table">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Category</th>
            </tr>
        </thead>

        <tbody>
            <?php if(mysqli_num_rows($products) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($products)): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($row['ProductName']); ?></strong>
                        </td>
                        <td>
                            $<?php echo number_format($row['Price'], 2); ?>
                        </td>
                        <td>
                            <?php echo $row['StockQuantity']; ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($row['CategoryName'] ?? 'Uncategorized'); ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align:center; color:#888;">
                        No products listed yet.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="page-footer">
        &copy; <?php echo date('Y'); ?> Online Marketplace &mdash; Seller Portal
    </div>

</body>
</html>

<style>
    .table-title {
        justify-self: center;
        width: 45vw;
    }

    .records-table { 
        justify-self: center;
        width: 45vw; 
        border-collapse: collapse; 
        margin-top: 15px; 
        margin-bottom: 30px; 
        background: white; 
        border-radius: 12px; 
        overflow: hidden; 
        box-shadow: 0 4px 16px rgba(0,0,0,0.05);
    }

    .records-table th, 
    .records-table td { 
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

    .records-table tr:last-child td { 
        border-bottom: none; 
    }
</style>