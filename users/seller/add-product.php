<?php
  session_start();
  if (!isset($_SESSION['userId'])) {
    header("Location: /OnlineMarketplace/login.php");
    exit();
  }

  $con = mysqli_connect("127.0.0.1","root","","online_marketplace") or die("Connection Error");
  $userId   = $_SESSION['userId'];
  $username = $_SESSION['uname'];
  $msg = "";

  $categories = mysqli_query($con, "SELECT * FROM category");
  if(isset($_POST['btnSave'])){
    $productName = mysqli_real_escape_string($con, $_POST['txtEventName']);
    $price       = (float)$_POST['txtPrice'];
    $stock       = (int)$_POST['txtStock'];
    $category    = mysqli_real_escape_string($con, $_POST['txtRoomID']);
    $description = mysqli_real_escape_string($con, $_POST['txtDescription']);

    // Check if product already exists
    $sql = "SELECT * FROM product WHERE productName=?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $productName);
    $stmt->execute();
    $result=$stmt->get_result();
    
    if($result->num_rows >= 1){
        $msg = "Invalid! Product already exists.";
    } else {
        // Insert new product
        $sql  = "INSERT INTO product (ProductName, Price, StockQuantity , Description, SellerID, CategoryID) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("sdisii", $productName, $price, $stock, $description, $userId, $category);
 
        if($stmt->execute()){
            $msg = "Product saved successfully!";
        } else {
            $msg = $con->error;
        }
    }
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product</title>
    <link rel="stylesheet" href="../../style.css">
</head>
 
<body class="ap-page">
 
<!-- ── Navbar ─────────────────────────────────────────── -->
<nav class="ap-nav">
    <div class="ap-nav-logo">
        <span class="ap-nav-orb">OM</span>
        Online Marketplace
    </div>
    <span class="ap-nav-badge">Seller Portal</span>
</nav>
 
<!-- ── Centered Card ──────────────────────────────────── -->
<div class="ap-wrap">
    <div class="ap-card">
 
        <!-- Card header -->
        <div class="ap-card-header">
            <h2 class="ap-title">Add Product</h2>
            <span class="ap-tag">MANAGEMENT</span>
        </div>
 
        <!-- Success / error message -->
        <?php if (!empty($msg)): ?>
        <div class="ap-alert">
            <?php echo htmlspecialchars($msg); ?>
        </div>
        <?php endif; ?>
 
        <!-- Form -->
        <form method="post" class="ap-form">
 
            <label class="ap-label">PRODUCT NAME</label>
            <input class="ap-input" type="text" name="txtEventName" placeholder="e.g. Wireless Headphones" required>
 
            <label class="ap-label">PRICE ($)</label>
            <input class="ap-input" type="number" name="txtPrice" placeholder="0.00" min="0" step="0.01" required>
 
            <label class="ap-label">STOCK / QUANTITY</label>
            <input class="ap-input" type="number" name="txtStock" placeholder="e.g. 100" min="1" required>
 
            <label class="ap-label">CATEGORY</label>
            <select class="ap-input" name="txtRoomID" required>
                <option value="" disabled selected>Select a category</option>
                <?php while($category = mysqli_fetch_array($categories)): ?>
                    <option value="<?php echo $category['CategoryID']; ?>">
                        <?php echo $category['CategoryName']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
 
            <label class="ap-label">DESCRIPTION</label>
            <input class="ap-input" type="text" name="txtDescription" placeholder="Short product description" required>
 
            <button type="submit" name="btnSave" class="ap-btn">
                Save Product
            </button>

             <a href="/OnlineMarketplace/users/seller/seller-interface.php" class="ap-back">
                ← Return to Dashboard
            </a>
 
        </form>
 
    </div>
</div>
 
<!-- ── Footer ─────────────────────────────────────────── -->
<footer class="ap-footer">
    &copy; 2026 OnlineMarketplace Systems. All rights reserved.
</footer>
 
</body>
</html>
 
<style>
/* ── Page shell ─────────────────────────────────────── */
.ap-page {
    margin: 0;
    min-height: 100vh;
    background: #eceef3;
    display: flex;
    flex-direction: column;
    font-family: Arial, Helvetica, sans-serif;
}
 
/* ── Navbar ─────────────────────────────────────────── */
.ap-nav {
    background: #1e3fd4;
    padding: 0 28px;
    height: 52px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
}
 
.ap-nav-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    color: white;
    font-size: 15px;
    font-weight: bold;
}
 
.ap-nav-orb {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(255,255,255,0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: bold;
    color: white;
    border: 1.5px solid rgba(255,255,255,0.5);
}
 
.ap-nav-badge {
    background: white;
    color: #1e3fd4;
    font-size: 12px;
    font-weight: bold;
    padding: 5px 14px;
    border-radius: 20px;
}
 
/* ── Center wrapper ─────────────────────────────────── */
.ap-wrap {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding: 48px 16px 32px;
}
 
/* ── Card ───────────────────────────────────────────── */
.ap-card {
    background: white;
    border-radius: 14px;
    width: 100%;
    max-width: 500px;
    padding: 32px 36px 36px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.09);
}
 
/* ── Card header row ────────────────────────────────── */
.ap-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 22px;
}
 
.ap-title {
    font-size: 20px;
    font-weight: bold;
    color: #1a1a1a;
    margin: 0;
}
 
.ap-tag {
    background: #eef0fb;
    color: #3a50c8;
    font-size: 11px;
    font-weight: bold;
    padding: 5px 12px;
    border-radius: 20px;
    letter-spacing: 0.04em;
}
 
/* ── Alert banner ───────────────────────────────────── */
.ap-alert {
    background: #edf7ed;
    color: #2e7d32;
    border-radius: 8px;
    padding: 12px 16px;
    font-size: 13px;
    text-align: center;
    margin-bottom: 20px;
}
 
/* ── Form ───────────────────────────────────────────── */
.ap-form {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
 
.ap-label {
    font-size: 11px;
    font-weight: bold;
    color: #888;
    letter-spacing: 0.06em;
    margin-top: 10px;
    margin-bottom: 2px;
    display: block;
}
 
.ap-input {
    width: 100%;
    padding: 11px 14px;
    border: 1px solid #dde0e8;
    border-radius: 8px;
    font-size: 14px;
    color: #333;
    background: #fafbfc;
    outline: none;
    transition: border-color 0.18s, background 0.18s;
    box-sizing: border-box;
}
 
.ap-input:focus {
    border-color: #1e6df6;
    background: #fff;
}
 
/* ── Submit button ──────────────────────────────────── */
.ap-btn {
    width: 100%;
    margin-top: 20px;
    padding: 14px;
    background: #1e3fd4;
    color: white;
    font-size: 15px;
    font-weight: bold;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: opacity 0.18s;
}
 
.ap-btn:hover {
    opacity: 0.88;
}
 
/* ── Back link ──────────────────────────────────────── */
.ap-back {
    display: block;
    text-align: left;
    margin-top: 32px;
    font-size: 13px;
    color: #1e2347;
    text-decoration: none;
}

.ap-back:hover {
    color: #949dd1;
    text-decoration: underline;
}

/* ── Footer ─────────────────────────────────────────── */
.ap-footer {
    text-align: center;
    color: #aaa;
    font-size: 12px;
    padding: 16px;
    flex-shrink: 0;
}
</style>