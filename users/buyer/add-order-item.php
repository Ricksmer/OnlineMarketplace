<?php
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

include("db_connection.php");
$message = "";




// Fetch data for dropdowns
$orders   = mysqli_query($con, "SELECT OrderID FROM `Order` ORDER BY OrderID DESC");
$products = mysqli_query($con, "SELECT ProductName, Price FROM Product");

// Add New Record Logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnSave'])) {
    $orderID = (int)$_POST['ddlOrderID'];
    $pName   = mysqli_real_escape_string($con, $_POST['ddlProductName']);
    $qty     = (int)$_POST['txtQuantity'];

    // Fetch unit price
    $priceLookup = mysqli_query($con, "SELECT Price FROM Product WHERE ProductName = '$pName'");
    $productData = mysqli_fetch_assoc($priceLookup);

    if ($productData) {
        $totalPrice = $productData['Price'] * $qty;

        $sql  = "INSERT INTO OrderItem (OrderID, ProductName, Quantity, PriceAtPurchase) VALUES (?, ?, ?, ?)";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("isid", $orderID, $pName, $qty, $totalPrice);

        if ($stmt->execute()) {
            $message = "<div class='alert success'>✅ Item added successfully! Total: $" . number_format($totalPrice, 2) . "</div>";
        } else {
            $message = "<div class='alert error'>❌ Error: " . $con->error . "</div>";
        }
        $stmt->close();
    } else {
        $message = "<div class='alert error'>❌ Product not found.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Order Item | Online Marketplace</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --blue:       #0a4cd3;
            --blue-light: #1e6df6;
            --surface:    #ffffff;
            --bg:         #f0f2f8;
            --text:       #111827;
            --muted:      #6b7280;
            --border:     #e5e7eb;
            --radius:     14px;
            --shadow:     0 4px 24px rgba(10,76,211,.10);
        }

        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }

        /* ── Navbar ── */
        .navbar {
            background: var(--blue); padding: 0 32px; height: 60px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 12px rgba(0,0,0,.15);
        }
        .nav-logo { color: #fff; font-weight: 800; font-size: 17px; display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .nav-logo .icon { width: 34px; height: 34px; border-radius: 50%; background: #fff; color: var(--blue); font-weight: 800; font-size: 13px; display: flex; align-items: center; justify-content: center; }
        .nav-right { display: flex; align-items: center; gap: 10px; }
        .nav-back { background: rgba(255,255,255,.15); color: #fff; padding: 6px 14px; border-radius: 8px; font-weight: 600; font-size: 13px; text-decoration: none; transition: .2s; }
        .nav-back:hover { background: rgba(255,255,255,.25); }
        .nav-btn { background: #fff; color: var(--blue); padding: 6px 14px; border-radius: 8px; font-weight: 700; font-size: 13px; text-decoration: none; transition: .2s; }
        .nav-btn:hover { background: #ff4d4d; color: #fff; }

        /* ── Page ── */
        .page { max-width: 560px; margin: 48px auto; padding: 0 20px; }

        .card { background: var(--surface); border-radius: 18px; padding: 36px; box-shadow: var(--shadow); }

        .card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; }
        .card-header h1 { font-size: 22px; font-weight: 800; }
        .badge { background: #e0e7ff; color: var(--blue); font-size: 11px; font-weight: 700; padding: 5px 12px; border-radius: 20px; letter-spacing: .05em; text-transform: uppercase; }

        /* Alerts */
        .alert { padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; font-weight: 500; }
        .success { background: #ecfdf5; color: #065f46; border: 1.5px solid #a7f3d0; }
        .error   { background: #fef2f2; color: #991b1b; border: 1.5px solid #fecaca; }

        /* Form */
        .form-group { margin-bottom: 18px; }
        .form-label { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: var(--muted); margin-bottom: 8px; }
        .form-input, select.form-input {
            width: 100%; padding: 12px 14px; border: 1.5px solid var(--border);
            border-radius: 10px; font-size: 14px; font-family: inherit;
            outline: none; transition: border-color .2s; background: #fafafa;
        }
        .form-input:focus, select.form-input:focus { border-color: var(--blue); background: #fff; }

        /* Buttons */
        .btn-save { margin-top: 8px; width: 100%; background: linear-gradient(135deg, var(--blue-light), var(--blue)); color: #fff; border: none; padding: 14px; border-radius: 10px; font-size: 15px; font-weight: 700; cursor: pointer; font-family: inherit; transition: .2s; }
        .btn-save:hover { opacity: .9; }

        .view-link { display: block; text-align: center; margin-top: 14px; color: var(--muted); font-size: 13px; text-decoration: none; }
        .view-link:hover { color: var(--blue); }

        footer { text-align: center; color: var(--muted); font-size: 12px; padding: 24px 0 12px; }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="dashboard.php" class="nav-logo">
        <span class="icon">OM</span>
        Online Marketplace
    </a>
    <div class="nav-right">
        <a href="index.php" class="nav-back">← Dashboard</a>
        <a href="logout.php" class="nav-btn">Logout</a>
    </div>
</nav>

<div class="page">
    <div class="card">
        <div class="card-header">
            <h1>Add Order Item</h1>
            <span class="badge">Management</span>
        </div>

        <?php echo $message; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Order Reference</label>
                <select name="ddlOrderID" class="form-input" required>
                    <option value="" disabled selected>-- Select Order ID --</option>
                    <?php while($o = mysqli_fetch_assoc($orders)): ?>
                        <option value="<?php echo $o['OrderID']; ?>">Order #<?php echo $o['OrderID']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Select Product</label>
                <select name="ddlProductName" class="form-input" required>
                    <option value="" disabled selected>-- Choose Product --</option>
                    <?php while($p = mysqli_fetch_assoc($products)): ?>
                        <option value="<?php echo $p['ProductName']; ?>">
                            <?php echo $p['ProductName']; ?> ($<?php echo number_format($p['Price'], 2); ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Quantity</label>
                <input class="form-input" type="number" name="txtQuantity" min="1" value="1" required>
            </div>

            <button type="submit" name="btnSave" class="btn-save">Save New Record</button>
        </form>

        <a href="view-order-items.php" class="view-link">📋 View all order items →</a>
    </div>
</div>



</body>
</html>