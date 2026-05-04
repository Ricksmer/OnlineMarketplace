<?php

$con = mysqli_connect("127.0.0.1","root","","online_marketplace") or die("Connection Error");

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $voucherID = $_POST['voucherID'];
    $code = $_POST['code'];
    $discountAmount = $_POST['discountAmount'];
    $expDay = $_POST['expDay'];
    $expMonth = $_POST['expMonth'];
    $expYear = $_POST['expYear'];
    $usageLimit = $_POST['usageLimit'];

    // SQL statement based on schema: VoucherID, Code, DiscountAmount, ExpirationDay, ExpirationMonth, ExpirationYear, UsageLimit
    $sql = "INSERT INTO Voucher (VoucherID, Code, DiscountAmount, ExpirationDay, ExpirationMonth, ExpirationYear, UsageLimit) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("isdiiii", $voucherID, $code, $discountAmount, $expDay, $expMonth, $expYear, $usageLimit);

    if ($stmt->execute()) {
        $message = "<div class='alert success'>Voucher '$code' added successfully!</div>";
    } else {
        $message = "<div class='alert error'>Error: " . $stmt->error . "</div>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Voucher | Online Marketplace</title>
</head>
<body>

    <nav class="navbar">
        <a href="#" class="nav-logo">
            <span class="logo-icon">OM</span>
            Online Marketplace
        </a>
        <div class="nav-badge">Seller Portal</div>
    </nav>

    <div class="page-wrapper">
        <div class="action-card">
            <div class="welcome-banner">
                <div>
                    <h1>Add Voucher</h1>
                </div>
                <span class="role-badge">MANAGEMENT</span>
            </div>

            <?php echo $message; ?>

            <form action="add-voucher.php" method="POST">
                <div class="form-group">
                    <label class="form-label">Voucher ID</label>
                    <input type="number" name="voucherID" placeholder="e.g. 501" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Promo Code</label>
                    <input type="text" name="code" maxlength="15" placeholder="SUMMER2026" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Discount Amount ($)</label>
                    <input type="number" step="0.01" name="discountAmount" placeholder="0.00" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Expiration (Day / Month / Year)</label>
                    <div class="expiry-grid">
                        <input type="number" name="expDay" min="1" max="31" placeholder="DD" required>
                        <input type="number" name="expMonth" min="1" max="12" placeholder="MM" required>
                        <input type="number" name="expYear" min="2026" placeholder="YYYY" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Usage Limit</label>
                    <input type="number" name="usageLimit" placeholder="Number of times usable" required>
                </div>

                <button type="submit" class="card-btn">Register Voucher</button>
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

        input {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #e6e9ef;
            border-radius: 10px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }

        input:focus {
            border-color: #0a4cd3;
        }

        .expiry-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 12px;
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

        /* ===================== ALERTS ===================== */
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }
        .success { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .error { background: #fde8e8; color: #c0392b; border: 1px solid #f8b4b4; }

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
        .back-link:hover { text-decoration: underline; }
    </style>