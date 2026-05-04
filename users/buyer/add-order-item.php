<?php
    session_start();
    $con = mysqli_connect("127.0.0.1", "root", "", "online_marketplace") or die("Error in connection.");

    if(!isset($_SESSION['userId'])){
        header("Location: /OnlineMarketplace/login.php");
        exit();
    }

    $userId = $_SESSION['userId'];
    $uname  = $_SESSION['uname'];
    $str     = "";
    $success = "";

    // Get product from URL
    $productName = isset($_GET['product']) ? trim($_GET['product']) : '';

    if(empty($productName)){
        header("Location: /OnlineMarketplace/users/buyer/market.php");
        exit();
    }

    // Fetch product details
    $stmtProd = $con->prepare("SELECT * FROM Product WHERE ProductName=?");
    $stmtProd->bind_param("s", $productName);
    $stmtProd->execute();
    $product = $stmtProd->get_result()->fetch_assoc();

    if(!$product){
        header("Location: /OnlineMarketplace/users/buyer/market.php");
        exit();
    }

    // Handle Add to Cart
    if(isset($_POST['btnAddToCart'])){
        $qty = intval($_POST['txtQuantity']);

        if($qty < 1){
            $str = "Quantity must be at least 1.";
        } elseif($qty > $product['StockQuantity']){
            $str = "Not enough stock. Only " . $product['StockQuantity'] . " available.";
        } else {
            $priceAtPurchase = $product['Price'];

            // Check for existing pending order for this buyer
            $stmtCheck = $con->prepare("SELECT OrderID FROM `Order` WHERE BuyerID=? AND Status='pending' LIMIT 1");
            $stmtCheck->bind_param("i", $userId);
            $stmtCheck->execute();
            $existingOrder = $stmtCheck->get_result()->fetch_assoc();

            if($existingOrder){
                // Use existing pending order
                $orderId = $existingOrder['OrderID'];
            } else {
                // Grab a random shipping record with status Processing
                $stmtShip = $con->prepare("SELECT ShippingID FROM Shipping WHERE ShippingStatus='Processing' ORDER BY RAND() LIMIT 1");
                $stmtShip->execute();
                $shipRow = $stmtShip->get_result()->fetch_assoc();

                if(!$shipRow){
                    $str = "No shipping option available. Please contact support.";
                    goto end;
                }

                $shippingId = $shipRow['ShippingID'];
                $today      = date('Y-m-d');

                // Create new order
                $stmtOrder = $con->prepare("INSERT INTO `Order` (OrderDate, Status, ShippingID, BuyerID) VALUES (?, 'pending', ?, ?)");
                $stmtOrder->bind_param("sii", $today, $shippingId, $userId);
                if(!$stmtOrder->execute()){
                    $str = "Failed to create order. Please try again.";
                    goto end;
                }
                $orderId = $con->insert_id;
            }

            // Insert order item
            $stmtItem = $con->prepare("INSERT INTO OrderItem (OrderID, ProductName, Quantity, PriceAtPurchase) VALUES (?, ?, ?, ?)");
            $stmtItem->bind_param("isid", $orderId, $productName, $qty, $priceAtPurchase);
            if($stmtItem->execute()){
                $success = "Added to cart! (" . $qty . "x " . htmlspecialchars($productName) . " @ $" . number_format($priceAtPurchase, 2) . " each)";
            } else {
                $str = "Failed to add item. Please try again.";
            }
        }
        end:
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add to Cart | Online Marketplace</title>
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

        /* Navbar */
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

        /* Page */
        .page { max-width: 520px; margin: 48px auto; padding: 0 20px; }

        .card { background: var(--surface); border-radius: 18px; padding: 36px; box-shadow: var(--shadow); }

        .card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
        .card-header h1 { font-size: 22px; font-weight: 800; }
        .badge { background: #e0e7ff; color: var(--blue); font-size: 11px; font-weight: 700; padding: 5px 12px; border-radius: 20px; letter-spacing: .05em; text-transform: uppercase; }

        /* Product info box */
        .product-info {
            background: #f0f4ff;
            border: 1.5px solid #c7d2fe;
            border-radius: 12px;
            padding: 16px 18px;
            margin-bottom: 24px;
        }
        .product-info-name {
            font-size: 17px; font-weight: 800; color: var(--text); margin-bottom: 6px;
        }
        .product-info-row {
            display: flex; justify-content: space-between; align-items: center;
        }
        .product-info-price {
            font-size: 22px; font-weight: 800; color: var(--blue);
        }
        .product-info-stock {
            font-size: 12px; color: var(--muted); font-weight: 500;
        }
        .product-info-stock.low { color: #ef4444; }
        .product-info-desc {
            font-size: 13px; color: var(--muted); margin-top: 8px; line-height: 1.5;
        }

        /* Alerts */
        .alert { padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; font-weight: 500; }
        .alert-success { background: #ecfdf5; color: #065f46; border: 1.5px solid #a7f3d0; }
        .alert-error   { background: #fef2f2; color: #991b1b; border: 1.5px solid #fecaca; }

        /* Form */
        .form-group { margin-bottom: 18px; }
        .form-label { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: var(--muted); margin-bottom: 8px; }
        .form-input {
            width: 100%; padding: 12px 14px; border: 1.5px solid var(--border);
            border-radius: 10px; font-size: 14px; font-family: inherit;
            outline: none; transition: border-color .2s; background: #fafafa;
        }
        .form-input:focus { border-color: var(--blue); background: #fff; }

        .price-note {
            font-size: 12px; color: var(--muted); margin-top: 6px;
        }

        /* Buttons */
        .btn-save {
            margin-top: 8px; width: 100%;
            background: linear-gradient(135deg, var(--blue-light), var(--blue));
            color: #fff; border: none; padding: 14px; border-radius: 10px;
            font-size: 15px; font-weight: 700; cursor: pointer; font-family: inherit; transition: .2s;
        }
        .btn-save:hover { opacity: .9; }

        .back-link {
            display: block; text-align: center; margin-top: 14px;
            color: var(--muted); font-size: 13px; text-decoration: none;
        }
        .back-link:hover { color: var(--blue); }

        .divider { border: none; border-top: 1px solid var(--border); margin: 20px 0; }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="/OnlineMarketplace/users/buyer/market.php" class="nav-logo">
        <span class="icon">OM</span>
        Online Marketplace
    </a>
    <div class="nav-right">
        <a href="/OnlineMarketplace/users/buyer/market.php" class="nav-back">← Back to Market</a>
        <a href="/OnlineMarketplace/logout.php" class="nav-btn">Logout</a>
    </div>
</nav>

<div class="page">
    <div class="card">
        <div class="card-header">
            <h1>Add to Cart</h1>
            <span class="badge">🛒 Cart</span>
        </div>

        <!-- Product Info Display -->
        <div class="product-info">
            <div class="product-info-name">🛍️ <?php echo htmlspecialchars($product['ProductName']); ?></div>
            <?php if(!empty($product['Description'])): ?>
                <div class="product-info-desc"><?php echo htmlspecialchars($product['Description']); ?></div>
            <?php endif; ?>
            <hr style="border:none;border-top:1px solid #c7d2fe;margin:10px 0;">
            <div class="product-info-row">
                <span class="product-info-price">$<?php echo number_format($product['Price'], 2); ?> <span style="font-size:13px;font-weight:500;color:var(--muted);">/ item</span></span>
                <span class="product-info-stock <?php echo $product['StockQuantity'] <= 5 ? 'low' : ''; ?>">
                    <?php echo $product['StockQuantity']; ?> in stock
                </span>
            </div>
        </div>

        <!-- Alerts -->
        <?php if(!empty($success)): ?>
            <div class="alert alert-success">✅ <?php echo $success; ?></div>
        <?php endif; ?>
        <?php if(!empty($str)): ?>
            <div class="alert alert-error">❌ <?php echo htmlspecialchars($str); ?></div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Quantity</label>
                <input class="form-input" type="number" name="txtQuantity" 
                       min="1" max="<?php echo $product['StockQuantity']; ?>" 
                       value="1" required>
                <p class="price-note">Price per item: $<?php echo number_format($product['Price'], 2); ?></p>
            </div>

            <button type="submit" name="btnAddToCart" class="btn-save">🛒 Add to Cart</button>
        </form>

        <a href="/OnlineMarketplace/users/buyer/market.php" class="back-link">← Back to Market</a>
    </div>
</div>

</body>
</html>
