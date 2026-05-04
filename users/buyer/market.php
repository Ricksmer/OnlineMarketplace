<?php
    session_start();
    $con = mysqli_connect("127.0.0.1", "root", "", "online_marketplace") or die("Error in connection.");

    if(!isset($_SESSION['userId'])){
        header("Location: /OnlineMarketplace/login.php");
        exit();
    }

    $userId = $_SESSION['userId'];
    $uname  = $_SESSION['uname'];

    // Fetch all products with stock > 0
    $stmtProducts = $con->prepare("SELECT p.ProductName, p.Price, p.StockQuantity, p.Description, c.CategoryName 
                                   FROM Product p 
                                   LEFT JOIN Category c ON p.CategoryID = c.CategoryID 
                                   ORDER BY p.ProductName ASC");
    $stmtProducts->execute();
    $productsResult = $stmtProducts->get_result();
    $products = [];
    while($row = $productsResult->fetch_assoc()){
        $products[] = $row;
    }

    // Fetch categories for filter
    $stmtCats = $con->prepare("SELECT * FROM Category ORDER BY CategoryName ASC");
    $stmtCats->execute();
    $catsResult = $stmtCats->get_result();
    $categories = [];
    while($row = $catsResult->fetch_assoc()){
        $categories[] = $row;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Market | Online Marketplace</title>
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
        .nav-user { color: rgba(255,255,255,.8); font-size: 13px; font-weight: 500; }
        .nav-back { background: rgba(255,255,255,.15); color: #fff; padding: 6px 14px; border-radius: 8px; font-weight: 600; font-size: 13px; text-decoration: none; transition: .2s; }
        .nav-back:hover { background: rgba(255,255,255,.25); }
        .nav-btn { background: #fff; color: var(--blue); padding: 6px 14px; border-radius: 8px; font-weight: 700; font-size: 13px; text-decoration: none; transition: .2s; }
        .nav-btn:hover { background: #ff4d4d; color: #fff; }

        /* Page */
        .page { max-width: 1100px; margin: 0 auto; padding: 36px 20px; }

        .page-header { margin-bottom: 28px; }
        .page-header h1 { font-size: 26px; font-weight: 800; color: var(--text); }
        .page-header p { color: var(--muted); font-size: 14px; margin-top: 4px; }

        /* Search & Filter Bar */
        .toolbar {
            display: flex; gap: 12px; margin-bottom: 28px; flex-wrap: wrap;
        }
        .search-box {
            flex: 1; min-width: 200px; padding: 11px 16px;
            border: 1.5px solid var(--border); border-radius: 10px;
            font-size: 14px; font-family: inherit; outline: none;
            background: #fff; transition: border-color .2s;
        }
        .search-box:focus { border-color: var(--blue); }
        .filter-select {
            padding: 11px 16px; border: 1.5px solid var(--border); border-radius: 10px;
            font-size: 14px; font-family: inherit; outline: none;
            background: #fff; transition: border-color .2s; cursor: pointer;
        }
        .filter-select:focus { border-color: var(--blue); }

        /* Product Grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
        }

        .product-card {
            background: var(--surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: transform .2s, box-shadow .2s;
            display: flex;
            flex-direction: column;
        }
        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 32px rgba(10,76,211,.15);
        }

        .product-img {
            background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
            height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
        }

        .product-body { padding: 18px; flex: 1; display: flex; flex-direction: column; }

        .product-category {
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .07em; color: var(--blue);
            background: #e0e7ff; border-radius: 20px;
            padding: 3px 10px; display: inline-block; margin-bottom: 8px;
        }

        .product-name {
            font-size: 15px; font-weight: 700; color: var(--text);
            margin-bottom: 6px; line-height: 1.3;
        }

        .product-desc {
            font-size: 12px; color: var(--muted); line-height: 1.5;
            margin-bottom: 12px; flex: 1;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }

        .product-footer {
            display: flex; align-items: center; justify-content: space-between;
            margin-top: auto;
        }

        .product-price {
            font-size: 18px; font-weight: 800; color: var(--blue);
        }

        .product-stock {
            font-size: 11px; color: var(--muted); font-weight: 500;
        }
        .product-stock.low { color: #ef4444; }

        .btn-add {
            margin-top: 14px; width: 100%;
            background: linear-gradient(135deg, var(--blue-light), var(--blue));
            color: #fff; border: none; padding: 11px;
            border-radius: 10px; font-size: 13px; font-weight: 700;
            cursor: pointer; font-family: inherit; transition: .2s;
            text-decoration: none; display: block; text-align: center;
        }
        .btn-add:hover { opacity: .9; }
        .btn-add.out-of-stock {
            background: #e5e7eb; color: var(--muted); cursor: not-allowed; pointer-events: none;
        }

        /* Empty state */
        .empty-state {
            text-align: center; padding: 60px 20px; color: var(--muted);
            grid-column: 1 / -1;
        }
        .empty-state .emoji { font-size: 48px; display: block; margin-bottom: 12px; }
        .empty-state p { font-size: 15px; }
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
        <h1>🛒 Market</h1>
        <p>Browse all available products</p>
    </div>

    <div class="toolbar">
        <input type="text" class="search-box" id="searchInput" placeholder="🔍  Search products...">
        <select class="filter-select" id="categoryFilter">
            <option value="">All Categories</option>
            <?php foreach($categories as $cat): ?>
                <option value="<?php echo htmlspecialchars($cat['CategoryName']); ?>">
                    <?php echo htmlspecialchars($cat['CategoryName']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="product-grid" id="productGrid">
        <?php if(count($products) === 0): ?>
            <div class="empty-state">
                <span class="emoji">📦</span>
                <p>No products available right now.</p>
            </div>
        <?php else: ?>
            <?php foreach($products as $p): ?>
            <div class="product-card" 
                 data-name="<?php echo strtolower(htmlspecialchars($p['ProductName'])); ?>"
                 data-category="<?php echo htmlspecialchars($p['CategoryName'] ?? ''); ?>">
                <div class="product-img">🛍️</div>
                <div class="product-body">
                    <?php if(!empty($p['CategoryName'])): ?>
                        <span class="product-category"><?php echo htmlspecialchars($p['CategoryName']); ?></span>
                    <?php endif; ?>
                    <div class="product-name"><?php echo htmlspecialchars($p['ProductName']); ?></div>
                    <div class="product-desc"><?php echo htmlspecialchars($p['Description'] ?? 'No description available.'); ?></div>
                    <div class="product-footer">
                        <span class="product-price">$<?php echo number_format($p['Price'], 2); ?></span>
                        <span class="product-stock <?php echo $p['StockQuantity'] <= 5 ? 'low' : ''; ?>">
                            <?php echo $p['StockQuantity'] > 0 ? $p['StockQuantity'] . ' in stock' : 'Out of stock'; ?>
                        </span>
                    </div>
                    <?php if($p['StockQuantity'] > 0): ?>
                        <a href="/OnlineMarketplace/users/buyer/add-order-item.php?product=<?php echo urlencode($p['ProductName']); ?>&price=<?php echo $p['Price']; ?>" 
                           class="btn-add">Add to Cart</a>
                    <?php else: ?>
                        <span class="btn-add out-of-stock">Out of Stock</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    const searchInput     = document.getElementById('searchInput');
    const categoryFilter  = document.getElementById('categoryFilter');
    const cards           = document.querySelectorAll('.product-card');

    function filterProducts() {
        const query    = searchInput.value.toLowerCase();
        const category = categoryFilter.value.toLowerCase();

        cards.forEach(card => {
            const name     = card.dataset.name;
            const cat      = card.dataset.category.toLowerCase();
            const matchSearch   = name.includes(query);
            const matchCategory = category === '' || cat === category;
            card.style.display = (matchSearch && matchCategory) ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterProducts);
    categoryFilter.addEventListener('change', filterProducts);
</script>

</body>
</html>
