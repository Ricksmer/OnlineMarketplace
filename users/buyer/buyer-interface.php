<?php
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: ../../login.php");
    exit();
}

$con = mysqli_connect("127.0.0.1", "root", "", "online_marketplace") or die("Error in connection.");

$userId = $_SESSION['userId'];


$orders = mysqli_query($con, "
    SELECT 
        o.OrderID,
        o.OrderDate,
        o.TotalAmount,
        o.Status AS OrderStatus,
        s.TrackingNumber,
        s.ShippingStatus,
        s.EstimatedDelivery
    FROM `order` o
    LEFT JOIN shipping s ON o.ShippingID = s.ShippingID
    WHERE o.BuyerID = $userId
    ORDER BY o.OrderID DESC
");


$orderItems = mysqli_query($con, "
    SELECT 
        oi.OrderID,
        oi.ProductName,
        oi.Quantity,
        oi.PriceAtPurchase,
        (oi.Quantity * oi.PriceAtPurchase) AS Subtotal,
        c.CategoryName
    FROM orderitem oi
    INNER JOIN `order` o ON oi.OrderID = o.OrderID
    LEFT JOIN product p ON oi.ProductName = p.ProductName
    LEFT JOIN category c ON p.CategoryID = c.CategoryID
    WHERE o.BuyerID = $userId
    ORDER BY oi.OrderID DESC
");


$payments = mysqli_query($con, "
    SELECT 
        p.PaymentID,
        p.OrderID,
        p.PaymentAmount,
        p.PaymentMethod,
        p.Status AS PaymentStatus,
        v.Code AS VoucherCode
    FROM payment p
    INNER JOIN `order` o ON p.OrderID = o.OrderID
    LEFT JOIN voucher v ON p.VoucherID = v.VoucherID
    WHERE o.BuyerID = $userId
    ORDER BY p.PaymentID DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Dashboard</title>
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
        <li><span class="nav-badge buyer-badge">Buyer</span></li>
        <li><a href="../logout.php">Logout</a></li>
    </ul>
</nav>

<div class="page-wrapper">

    <div class="welcome-banner">
        <div>
            <h1>
                Welcome back, 
                <?php echo isset($_SESSION['uname']) ? htmlspecialchars($_SESSION['uname']) : 'Buyer'; ?>!
            </h1>
            <p>Browse, shop, and manage your orders below.</p>
        </div>

        <span class="role-badge buyer-badge">Buyer Account</span>
    </div>

    <p class="section-title">What would you like to do?</p>

    <div class="action-grid">

        <div class="action-card">
            <div class="card-icon icon-teal">&#127968;</div>
            <h3>Edit Profile</h3>
            <p>Customize your user profile.</p>
            <button class="card-btn" onclick="location.href='../edit-profile.php'">
                Go to Edit Profile
            </button>
        </div>

        <div class="action-card">
            <div class="card-icon icon-blue">&#128722;</div>
            <h3>View Marketplace</h3>
            <p>Browse products and add items to your cart.</p>
            <button class="card-btn" onclick="location.href='market.php'">
                Go to Marketplace
            </button>
        </div>

        <div class="action-card">
            <div class="card-icon icon-green">&#9989;</div>
            <h3>Checkout</h3>
            <p>Review your cart and place your order.</p>
            <button class="card-btn" onclick="location.href='checkout.php'">
                Go to Checkout
            </button>
        </div>

        <div class="action-card">
            <div class="card-icon icon-purple">&#128179;</div>
            <h3>Add Payment</h3>
            <p>Complete payment for your pending order.</p>
            <button class="card-btn" onclick="location.href='add-payment.php'">
                Go to Payment
            </button>
        </div>

    </div>

    <a href="../logout.php" class="logout-link">&larr; Logout</a>

</div>

<!-- BUYER ORDER RECORDS -->
<p class="section-title table-title">Your Orders</p>

<table class="records-table">
    <thead>
        <tr>
            <th>Order ID</th>
            <th>Order Date</th>
            <th>Total Amount</th>
            <th>Status</th>
            <th>Tracking No.</th>
            <th>Shipping Status</th>
            <th>Estimated Delivery</th>
        </tr>
    </thead>

    <tbody>
        <?php if ($orders && mysqli_num_rows($orders) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($orders)): ?>
                <tr>
                    <td>#<?php echo $row['OrderID']; ?></td>
                    <td><?php echo htmlspecialchars($row['OrderDate'] ?? 'N/A'); ?></td>
                    <td>$<?php echo number_format($row['TotalAmount'] ?? 0, 2); ?></td>
                    <td><?php echo htmlspecialchars($row['OrderStatus'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($row['TrackingNumber'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($row['ShippingStatus'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($row['EstimatedDelivery'] ?? 'N/A'); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" style="text-align:center; color:#888;">
                    No orders found.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- BUYER ORDER ITEMS -->
<p class="section-title table-title">Your Order Items</p>

<table class="records-table">
    <thead>
        <tr>
            <th>Order ID</th>
            <th>Product Name</th>
            <th>Category</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Subtotal</th>
        </tr>
    </thead>

    <tbody>
        <?php if ($orderItems && mysqli_num_rows($orderItems) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($orderItems)): ?>
                <tr>
                    <td>#<?php echo $row['OrderID']; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($row['ProductName']); ?></strong>
                    </td>
                    <td><?php echo htmlspecialchars($row['CategoryName'] ?? 'Uncategorized'); ?></td>
                    <td><?php echo $row['Quantity']; ?></td>
                    <td>$<?php echo number_format($row['PriceAtPurchase'], 2); ?></td>
                    <td>$<?php echo number_format($row['Subtotal'], 2); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" style="text-align:center; color:#888;">
                    No order items found.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- BUYER PAYMENT RECORDS -->
<p class="section-title table-title">Your Payments</p>

<table class="records-table">
    <thead>
        <tr>
            <th>Payment ID</th>
            <th>Order ID</th>
            <th>Amount</th>
            <th>Method</th>
            <th>Status</th>
            <th>Voucher</th>
        </tr>
    </thead>

    <tbody>
        <?php if ($payments && mysqli_num_rows($payments) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($payments)): ?>
                <tr>
                    <td><?php echo $row['PaymentID']; ?></td>
                    <td>#<?php echo $row['OrderID']; ?></td>
                    <td>$<?php echo number_format($row['PaymentAmount'], 2); ?></td>
                    <td><?php echo htmlspecialchars($row['PaymentMethod']); ?></td>
                    <td><?php echo htmlspecialchars($row['PaymentStatus']); ?></td>
                    <td>
                        <?php echo !empty($row['VoucherCode']) ? htmlspecialchars($row['VoucherCode']) : 'None'; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" style="text-align:center; color:#888;">
                    No payment records found.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<div class="page-footer">
    &copy; <?php echo date('Y'); ?> Online Marketplace &mdash; Buyer Portal
</div>

</body>
</html>

<style>
    .table-title {
        justify-self: center;
        width: 65vw;
    }

    .records-table {
        justify-self: center;
        width: 65vw;
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

    @media screen and (max-width: 900px) {
        .records-table,
        .table-title {
            width: 90vw;
        }

        .records-table {
            display: block;
            overflow-x: auto;
            white-space: nowrap;
        }
    }
</style>