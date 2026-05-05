<?php
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: ../../login.php");
    exit();
}

$con = mysqli_connect("127.0.0.1", "root", "", "online_marketplace") or die("Connection Error");

$orderQuery = "
    SELECT 
        o.OrderID, 
        o.TotalAmount, 
        o.Status,
        u.Username
    FROM `order` o
    LEFT JOIN `user` u ON o.BuyerID = u.UserID
    ORDER BY o.OrderID ASC
";

$orderResult = mysqli_query($con, $orderQuery);

if (!$orderResult) {
    die("Order query error: " . mysqli_error($con));
}

$voucherQuery = "
    SELECT 
        VoucherID, 
        Code 
    FROM voucher 
    ORDER BY VoucherID ASC
";

$voucherResult = mysqli_query($con, $voucherQuery);

if (!$voucherResult) {
    die("Voucher query error: " . mysqli_error($con));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Payment | Online Marketplace</title>
</head>

<body>

    <nav class="navbar">
        <a href="seller-interface.php" class="nav-logo">
            <span class="logo-icon">OM</span>
            Online Marketplace
        </a>
        <div class="nav-badge">Seller Portal</div>
    </nav>

    <div class="page-wrapper">
        <div class="action-card">

            <div class="welcome-banner">
                <div>
                    <h1>Add Payment</h1>
                </div>
                <span class="role-badge">MANAGEMENT</span>
            </div>

            <form action="save-payment.php" method="POST">

                <div class="form-group">
                    <label class="form-label">Payment ID</label>
                    <input type="number" name="paymentID" placeholder="e.g. 1001" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Order</label>
                    <select name="orderID" required>
                        <option value="">Select Order</option>

                        <?php while ($order = mysqli_fetch_assoc($orderResult)): ?>
                            <option value="<?php echo $order['OrderID']; ?>">
                                Order #<?php echo $order['OrderID']; ?>
                                —
                                <?php echo htmlspecialchars($order['Username'] ?? 'Unknown Buyer'); ?>
                                —
                                Total: $<?php echo number_format($order['TotalAmount'] ?? 0, 2); ?>
                                —
                                <?php echo htmlspecialchars($order['Status'] ?? 'No Status'); ?>
                            </option>
                        <?php endwhile; ?>

                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Voucher</label>
                    <select name="voucherID">
                        <option value="">No Voucher</option>

                        <?php while ($voucher = mysqli_fetch_assoc($voucherResult)): ?>
                            <option value="<?php echo $voucher['VoucherID']; ?>">
                                <?php echo htmlspecialchars($voucher['Code']); ?>
                            </option>
                        <?php endwhile; ?>

                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Payment Amount</label>
                    <input type="number" step="0.01" min="0.01" name="paymentAmount" placeholder="0.00" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Payment Method</label>
                    <select name="paymentMethod" required>
                        <option value="">Select Method</option>
                        <option value="Cash">Cash</option>
                        <option value="GCash">GCash</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Credit Card">Credit Card</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Payment Status</label>
                    <select name="paymentStatus" required>
                        <option value="">Select Status</option>
                        <option value="Pending">Pending</option>
                        <option value="Paid">Paid</option>
                        <option value="Failed">Failed</option>
                    </select>
                </div>

                <button type="submit" class="card-btn" name="btnSavePayment">
                    Save Payment
                </button>

            </form>

            <a href="seller-interface.php" class="back-link">← Go Back</a>

        </div>

        <footer class="page-footer">
            &copy; 2026 OnlineMarketplace Systems. All rights reserved.
        </footer>
    </div>

</body>
</html>

<style>
    /* ===================== RESET & BASE ===================== */
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
        font-family: Arial, Helvetica, sans-serif;
    }

    body {
        background: linear-gradient(135deg, #f2f4f8, #e6e9ef);
        min-height: 100vh;
        color: #1a1a2e;
    }

    /* ===================== NAVBAR ===================== */
    .navbar {
        background: #0a4cd3;
        padding: 12px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: sticky;
        top: 0;
        z-index: 100;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .nav-logo {
        color: white;
        font-size: 18px;
        font-weight: bold;
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
    }

    .nav-logo span.logo-icon {
        background: white;
        color: #0a4cd3;
        font-weight: bold;
        font-size: 14px;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .nav-badge {
        background: white;
        color: #0a4cd3;
        padding: 6px 14px;
        border-radius: 8px;
        font-weight: bold;
        font-size: 13px;
    }

    /* ===================== PAGE LAYOUT ===================== */
    .page-wrapper {
        max-width: 600px;
        margin: 40px auto;
        padding: 0 20px;
    }

    /* ===================== FORM CARD ===================== */
    .action-card {
        background: white;
        border-radius: 16px;
        padding: 32px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.07);
        border: 1.5px solid transparent;
    }

    .welcome-banner {
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .welcome-banner h1 {
        font-size: 22px;
        color: #1a1a2e;
    }

    .role-badge {
        background: #e8eaf5;
        color: #0a4cd3;
        font-size: 12px;
        font-weight: bold;
        padding: 6px 14px;
        border-radius: 20px;
    }

    /* ===================== FORM ELEMENTS ===================== */
    .form-group {
        margin-bottom: 18px;
    }

    .form-label {
        display: block;
        font-size: 13px;
        font-weight: bold;
        text-transform: uppercase;
        color: #888;
        margin-bottom: 8px;
        letter-spacing: 0.05em;
    }

    input,
    select {
        width: 100%;
        padding: 12px 16px;
        border: 1.5px solid #e6e9ef;
        border-radius: 10px;
        font-size: 14px;
        outline: none;
        transition: border-color 0.2s;
        background: white;
    }

    input:focus,
    select:focus {
        border-color: #0a4cd3;
    }

    .card-btn {
        margin-top: 10px;
        background: linear-gradient(135deg, #1e6df6, #0a4cd3);
        color: white;
        border: none;
        padding: 14px;
        border-radius: 10px;
        font-size: 15px;
        font-weight: bold;
        cursor: pointer;
        width: 100%;
        transition: opacity 0.18s;
    }

    .card-btn:hover {
        opacity: 0.88;
    }

    .page-footer {
        text-align: center;
        padding: 20px;
        color: #aaa;
        font-size: 12px;
    }

    .back-link {
        display: block;
        text-align: center;
        margin-top: 16px;
        font-size: 13px;
        color: #1e6df6;
        text-decoration: none;
    }

    .back-link:hover {
        text-decoration: underline;
    }
</style>