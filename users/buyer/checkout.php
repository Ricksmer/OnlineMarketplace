<?php
    session_start();
    $con = mysqli_connect("127.0.0.1", "root", "", "online_marketplace") or die("Error in connection.");

    if(!isset($_SESSION['userId'])){
        header("Location: /OnlineMarketplace/login.php");
        exit();
    }

    $userId = $_SESSION['userId'];
    $uname  = $_SESSION['uname'];
    $success = "";

    // Handle Checkout button
    if(isset($_POST['btnCheckout'])){
        $orderId = intval($_POST['orderIdToCheckout']);

        // Calculate total amount from order items
        $stmtTotal = $con->prepare("SELECT SUM(Quantity * PriceAtPurchase) as Total FROM OrderItem WHERE OrderID=?");
        $stmtTotal->bind_param("i", $orderId);
        $stmtTotal->execute();
        $totalRow = $stmtTotal->get_result()->fetch_assoc();
        $totalAmount = $totalRow['Total'] ?? 0;

        // Update order: set status to processing and update TotalAmount
        $stmtUpdate = $con->prepare("UPDATE `Order` SET Status='processing', TotalAmount=? WHERE OrderID=? AND BuyerID=?");
        $stmtUpdate->bind_param("dii", $totalAmount, $orderId, $userId);
        if($stmtUpdate->execute()){
            $success = "Order #" . $orderId . " has been placed! Total: $" . number_format($totalAmount, 2);
        }
    }

    // Fetch pending orders for this buyer
    $stmtOrders = $con->prepare("SELECT * FROM `Order` WHERE BuyerID=? AND Status='pending' ORDER BY OrderID DESC");
    $stmtOrders->bind_param("i", $userId);
    $stmtOrders->execute();
    $ordersResult = $stmtOrders->get_result();
    $orders = [];
    while($row = $ordersResult->fetch_assoc()){
        $orders[] = $row;
    }

    // Fetch order items for each order
    $orderItems = [];
    foreach($orders as $order){
        $oid = $order['OrderID'];
        $stmtItems = $con->prepare("SELECT * FROM OrderItem WHERE OrderID=?");
        $stmtItems->bind_param("i", $oid);
        $stmtItems->execute();
        $itemsResult = $stmtItems->get_result();
        $items = [];
        while($item = $itemsResult->fetch_assoc()){
            $items[] = $item;
        }
        $orderItems[$oid] = $items;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Online Marketplace</title>
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
            --green:      #065f46;
            --green-bg:   #ecfdf5;
            --green-border: #a7f3d0;
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
        .nav-user { color: rgba(255,255,255,.8); font-size: 13px; font-weight: 500; }
        .nav-back { background: rgba(255,255,255,.15); color: #fff; padding: 6px 14px; border-radius: 8px; font-weight: 600; font-size: 13px; text-decoration: none; transition: .2s; }
        .nav-back:hover { background: rgba(255,255,255,.25); }
        .nav-btn { background: #fff; color: var(--blue); padding: 6px 14px; border-radius: 8px; font-weight: 700; font-size: 13px; text-decoration: none; transition: .2s; }
        .nav-btn:hover { background: #ff4d4d; color: #fff; }

        /* Page */
        .page { max-width: 720px; margin: 48px auto; padding: 0 20px 60px; }

        .page-header { margin-bottom: 28px; }
        .page-header h1 { font-size: 26px; font-weight: 800; }
        .page-header p { color: var(--muted); font-size: 14px; margin-top: 4px; }

        /* Success alert */
        .alert-success {
            background: var(--green-bg); color: var(--green);
            border: 1.5px solid var(--green-border);
            border-radius: 12px; padding: 16px 20px;
            margin-bottom: 28px; font-size: 14px; font-weight: 600;
            display: flex; align-items: center; gap: 10px;
        }

        /* Empty state */
        .empty-state {
            background: var(--surface); border-radius: 18px;
            box-shadow: var(--shadow); padding: 60px 20px;
            text-align: center;
        }
        .empty-state .emoji { font-size: 52px; display: block; margin-bottom: 16px; }
        .empty-state h2 { font-size: 20px; font-weight: 800; margin-bottom: 8px; }
        .empty-state p { color: var(--muted); font-size: 14px; margin-bottom: 24px; }
        .btn-shop {
            display: inline-block;
            background: linear-gradient(135deg, var(--blue-light), var(--blue));
            color: #fff; text-decoration: none;
            padding: 13px 28px; border-radius: 10px;
            font-size: 15px; font-weight: 700; transition: .2s;
        }
        .btn-shop:hover { opacity: .9; }

        /* Order Card */
        .order-card {
            background: var(--surface);
            border-radius: 18px;
            box-shadow: var(--shadow);
            margin-bottom: 24px;
            overflow: hidden;
        }

        /* Order Header */
        .order-header {
            background: linear-gradient(135deg, var(--blue-light), var(--blue));
            padding: 16px 24px;
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 8px;
        }
        .order-header-left { display: flex; align-items: center; gap: 14px; }
        .order-id {
            color: #fff; font-size: 16px; font-weight: 800;
        }
        .order-status-badge {
            background: rgba(255,255,255,.2); color: #fff;
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .06em; padding: 4px 10px; border-radius: 20px;
        }
        .order-date {
            color: rgba(255,255,255,.75); font-size: 13px; font-weight: 500;
        }

        /* Order Items Table */
        .order-body { padding: 20px 24px; }

        .items-table {
            width: 100%; border-collapse: collapse; margin-bottom: 16px;
        }
        .items-table th {
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .07em; color: var(--muted);
            padding: 0 0 10px 0; text-align: left; border-bottom: 1.5px solid var(--border);
        }
        .items-table th:last-child { text-align: right; }
        .items-table td {
            padding: 12px 0; font-size: 14px; border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }
        .items-table td:last-child { text-align: right; font-weight: 700; color: var(--blue); }
        .items-table tr:last-child td { border-bottom: none; }

        .item-name { font-weight: 600; color: var(--text); }
        .item-meta { font-size: 12px; color: var(--muted); margin-top: 2px; }

        /* Order Footer */
        .order-footer {
            border-top: 1.5px solid var(--border);
            padding: 16px 24px;
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 12px;
        }
        .order-total {
            font-size: 18px; font-weight: 800; color: var(--text);
        }
        .order-total span { color: var(--blue); }

        .btn-checkout {
            background: linear-gradient(135deg, #16a34a, #15803d);
            color: #fff; border: none; padding: 12px 24px;
            border-radius: 10px; font-size: 14px; font-weight: 700;
            cursor: pointer; font-family: inherit; transition: .2s;
        }
        .btn-checkout:hover { opacity: .9; transform: translateY(-1px); }

        /* Auto-refresh overlay */
        .refresh-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,.35);
            z-index: 200;
            align-items: center; justify-content: center;
        }
        .refresh-overlay.show { display: flex; }
        .refresh-box {
            background: #fff; border-radius: 18px;
            padding: 36px 40px; text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,.2);
            max-width: 360px; width: 90%;
        }
        .refresh-box .big-emoji { font-size: 52px; display: block; margin-bottom: 12px; }
        .refresh-box h2 { font-size: 20px; font-weight: 800; margin-bottom: 8px; }
        .refresh-box p { color: var(--muted); font-size: 14px; margin-bottom: 20px; }
        .refresh-box .btn-market {
            display: inline-block;
            background: linear-gradient(135deg, var(--blue-light), var(--blue));
            color: #fff; text-decoration: none;
            padding: 12px 24px; border-radius: 10px;
            font-size: 14px; font-weight: 700; transition: .2s;
        }
        .refresh-box .btn-market:hover { opacity: .9; }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="/OnlineMarketplace/users/buyer/buyer-interface.php" class="nav-logo">
        <span class="icon">OM</span>
        Online Marketplace
    </a>
    <div class="nav-right">
        <span class="nav-user">👋 <?php echo htmlspecialchars($uname); ?></span>
        <a href="/OnlineMarketplace/users/buyer/buyer-interface.php" class="nav-back">← Back</a>
        <a href="/OnlineMarketplace/logout.php" class="nav-btn">Logout</a>
    </div>
</nav>

<div class="page">
    <div class="page-header">
        <h1>🧾 Checkout</h1>
        <p>Review your pending orders before placing them.</p>
    </div>

    <?php if(!empty($success)): ?>
        <div class="alert-success">
            ✅ <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <?php if(count($orders) === 0): ?>
        <!-- Empty state -->
        <div class="empty-state">
            <span class="emoji">🛍️</span>
            <h2>No pending orders</h2>
            <p>Looks like you haven't added anything to your cart yet.</p>
            <a href="/OnlineMarketplace/users/buyer/market.php" class="btn-shop">🛒 Browse the Market</a>
        </div>

    <?php else: ?>
        <?php foreach($orders as $order):
            $oid   = $order['OrderID'];
            $items = $orderItems[$oid] ?? [];
            $orderTotal = 0;
            foreach($items as $item){
                $orderTotal += $item['Quantity'] * $item['PriceAtPurchase'];
            }
        ?>
        <div class="order-card">
            <!-- Order Header -->
            <div class="order-header">
                <div class="order-header-left">
                    <span class="order-id">Order #<?php echo $oid; ?></span>
                    <span class="order-status-badge">⏳ Pending</span>
                </div>
                <span class="order-date">📅 <?php echo htmlspecialchars($order['OrderDate']); ?></span>
            </div>

            <!-- Order Items -->
            <div class="order-body">
                <?php if(count($items) === 0): ?>
                    <p style="color:var(--muted);font-size:14px;">No items in this order.</p>
                <?php else: ?>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th style="text-align:center;">Qty</th>
                            <th style="text-align:right;">Unit Price</th>
                            <th>Item Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($items as $item):
                            $itemTotal = $item['Quantity'] * $item['PriceAtPurchase'];
                        ?>
                        <tr>
                            <td>
                                <div class="item-name"><?php echo htmlspecialchars($item['ProductName']); ?></div>
                            </td>
                            <td style="text-align:center; color:var(--muted); font-weight:600;">
                                <?php echo $item['Quantity']; ?>
                            </td>
                            <td style="text-align:right; color:var(--muted);">
                                $<?php echo number_format($item['PriceAtPurchase'], 2); ?>
                            </td>
                            <td>$<?php echo number_format($itemTotal, 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <!-- Order Footer -->
            <div class="order-footer">
                <div class="order-total">
                    Order Total: <span>$<?php echo number_format($orderTotal, 2); ?></span>
                </div>
                <form method="POST" onsubmit="return confirmCheckout(<?php echo $oid; ?>)">
                    <input type="hidden" name="orderIdToCheckout" value="<?php echo $oid; ?>">
                    <button type="submit" name="btnCheckout" class="btn-checkout">
                        ✔ Checkout
                    </button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Success overlay shown after checkout -->
<?php if(!empty($success)): ?>
<div class="refresh-overlay show" id="successOverlay">
    <div class="refresh-box">
        <span class="big-emoji">🎉</span>
        <h2>Order Placed!</h2>
        <p><?php echo htmlspecialchars($success); ?></p>
        <a href="/OnlineMarketplace/users/buyer/market.php" class="btn-market">🛒 Browse the Market</a>
    </div>
</div>
<?php endif; ?>

<script>
    function confirmCheckout(orderId) {
        return confirm('Place Order #' + orderId + '? This will move it to processing.');
    }

    // Close overlay when clicking outside the box
    const overlay = document.getElementById('successOverlay');
    if(overlay){
        overlay.addEventListener('click', function(e){
            if(e.target === overlay) overlay.classList.remove('show');
        });
    }
</script>

</body>
</html>
