<?php
    session_start();
    $con = mysqli_connect("127.0.0.1","root","","online_marketplace") or die("Error in connection.");
    $str = "";
    $success = "";

    if(!isset($_SESSION['userId'])){
        header("Location: /OnlineMarketplace/login.php");
        exit();
    }

    $userId = $_SESSION['userId'];

    function safe_prepare($con, $sql) {
        $stmt = $con->prepare($sql);
        if ($stmt === false) {
            die("Query prepare failed: " . htmlspecialchars($con->error) . "<br>SQL: " . htmlspecialchars($sql));
        }
        return $stmt;
    }

    function loadAddresses($con, $userId) {
        $stmt = safe_prepare($con, "SELECT * FROM address WHERE UserID=? ORDER BY Country ASC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $addresses = [];
        while($row = $result->fetch_assoc()){
            $addresses[] = $row;
        }
        return $addresses;
    }

    // Check if buyer and get current preferred address
    $isBuyer = false;
    $currentPreferred = null;
    $stmtBuyer = safe_prepare($con, "SELECT PreferredShippingAddress FROM buyer WHERE UserID=?");
    $stmtBuyer->bind_param("i", $userId);
    $stmtBuyer->execute();
    $buyerRow = $stmtBuyer->get_result()->fetch_assoc();
    if($buyerRow){
        $isBuyer = true;
        $currentPreferred = $buyerRow['PreferredShippingAddress'];
    }

    $allAddresses = loadAddresses($con, $userId);

    // Set preferred address
    if(isset($_POST['btnSetPreferred'])){
        $prefCity    = trim($_POST['prefCity']);
        $prefCountry = trim($_POST['prefCountry']);
        $prefZip     = trim($_POST['prefZip']);
        $prefValue   = $prefCity . ',' . $prefCountry . ',' . $prefZip;

        $stmtPref = safe_prepare($con, "UPDATE buyer SET PreferredShippingAddress=? WHERE UserID=?");
        $stmtPref->bind_param("si", $prefValue, $userId);
        $stmtPref->execute();

        $success = "Preferred shipping address updated.";
        $currentPreferred = $prefValue;
    }

    if(isset($_POST['btnDeleteAddress'])){
        $delCity    = trim($_POST['delCity']);
        $delCountry = trim($_POST['delCountry']);
        $delZip     = trim($_POST['delZip']);

        $stmtDel = safe_prepare($con, "DELETE FROM address WHERE UserID=? AND City=? AND Country=? AND ZipCode=? LIMIT 1");
        $stmtDel->bind_param("isss", $userId, $delCity, $delCountry, $delZip);
        $stmtDel->execute();

        // If deleted address was the preferred one, clear it
        $deletedVal = $delCity . ',' . $delCountry . ',' . $delZip;
        if($isBuyer && $currentPreferred === $deletedVal){
            $stmtClear = safe_prepare($con, "UPDATE buyer SET PreferredShippingAddress=NULL WHERE UserID=?");
            $stmtClear->bind_param("i", $userId);
            $stmtClear->execute();
            $currentPreferred = null;
        }

        $success = "Address deleted successfully.";
        $allAddresses = loadAddresses($con, $userId);
    }

    if(isset($_POST['btnSaveAddress'])){
        $city    = trim($_POST['txtCity']);
        $country = trim($_POST['txtCountry']);
        $zip     = trim($_POST['txtZipCode']);

        if(empty($city) || empty($country) || empty($zip)){
            $str = "All fields are required.";
        } elseif(!is_numeric($zip)){
            $str = "Zip Code must be a number.";
        } else {
            $stmtChk = safe_prepare($con, "SELECT COUNT(*) as cnt FROM address WHERE UserID=? AND City=? AND Country=? AND ZipCode=?");
            $stmtChk->bind_param("isss", $userId, $city, $country, $zip);
            $stmtChk->execute();
            $dup = $stmtChk->get_result()->fetch_assoc();

            if($dup['cnt'] > 0){
                $str = "This address already exists.";
            } else {
                $stmtIns = safe_prepare($con, "INSERT INTO address (UserID, City, Country, ZipCode) VALUES (?, ?, ?, ?)");
                $stmtIns->bind_param("isss", $userId, $city, $country, $zip);

                if($stmtIns->execute()){
                    $success = "Address added successfully.";
                    $allAddresses = loadAddresses($con, $userId);
                } else {
                    $str = "Failed to save address. Please try again.";
                }
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Addresses</title>
    <link rel="stylesheet" href="/OnlineMarketplace/style.css">
    <style>
        .manage-addresses-card {
            background: white;
            padding: 40px;
            width: 30vw;
            border-radius: 18px;
            box-shadow: 0 25px 45px rgba(0,0,0,0.12);
        }

        .manage-addresses-card h2 {
            text-align: center;
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #0a4cd3;
            text-align: center;
        }
        .success-output {
            text-align: center;
            margin-top: 16px;
            color: green;
            margin-bottom: 20px;
            padding: 10px;
        }
        .address-list { margin-bottom: 20px; }
        .address-item {
            background: #f2f4f8;
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 10px;
            font-size: 14px;
            color: #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
        }
        .address-item.is-preferred {
            border: 2px solid #0a4cd3;
            background: #eef2ff;
        }
        .preferred-badge {
            font-size: 11px;
            background: #0a4cd3;
            color: white;
            border-radius: 4px;
            padding: 2px 7px;
            margin-left: 6px;
            vertical-align: middle;
        }
        .address-actions {
            display: flex;
            gap: 6px;
            align-items: center;
        }
        .btn-delete {
            background: none;
            border: 1px solid #e74c3c;
            color: #e74c3c;
            border-radius: 6px;
            padding: 4px 12px;
            cursor: pointer;
            font-size: 12px;
            transition: background 0.2s, color 0.2s;
        }
        .btn-delete:hover { background: #e74c3c; color: #fff; }
        .btn-set-preferred {
            background: none;
            border: 1px solid #0a4cd3;
            color: #0a4cd3;
            border-radius: 6px;
            padding: 4px 12px;
            cursor: pointer;
            font-size: 12px;
            transition: background 0.2s, color 0.2s;
        }
        .btn-set-preferred:hover { background: #0a4cd3; color: #fff; }
        .add-address-title {
            font-size: 15px;
            font-weight: bold;
            color: #333;
            margin-bottom: 12px;
            margin-top: 4px;
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
        .divider { border: none; border-top: 1px solid #eee; margin: 16px 0; }
        .no-address { text-align: center; font-size: 13px; color: #aaa; margin-bottom: 16px; }
    </style>
</head>
<body class="center-page">
<div class="manage-addresses-card">
    <h2 class="section-title">Manage Addresses</h2>

    <?php if(!empty($success)): ?>
        <div class="success-output"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <div class="error-output"><?php echo htmlspecialchars($str); ?></div>

    <?php if(count($allAddresses) > 0): ?>
    <div class="address-list">
        <?php foreach($allAddresses as $addr):
            $addrKey = $addr['City'] . ',' . $addr['Country'] . ',' . $addr['ZipCode'];
            $isPreferred = ($isBuyer && $currentPreferred === $addrKey);
        ?>
        <div class="address-item <?php echo $isPreferred ? 'is-preferred' : ''; ?>">
            <span>
                📍 <?php echo htmlspecialchars($addr['City']); ?>,
                <?php echo htmlspecialchars($addr['Country']); ?> —
                <?php echo htmlspecialchars($addr['ZipCode']); ?>
                <?php if($isPreferred): ?>
                    <span class="preferred-badge">✔ Preferred</span>
                <?php endif; ?>
            </span>
            <div class="address-actions">
                <!-- Set as Preferred (buyers only, only if not already preferred) -->
                <?php if($isBuyer && !$isPreferred): ?>
                <form method="post" style="margin:0;">
                    <input type="hidden" name="prefCity"    value="<?php echo htmlspecialchars($addr['City']); ?>">
                    <input type="hidden" name="prefCountry" value="<?php echo htmlspecialchars($addr['Country']); ?>">
                    <input type="hidden" name="prefZip"     value="<?php echo htmlspecialchars($addr['ZipCode']); ?>">
                    <button type="submit" class="btn-set-preferred" name="btnSetPreferred">Set Preferred</button>
                </form>
                <?php endif; ?>

                <!-- Delete -->
                <form method="post" style="margin:0;">
                    <input type="hidden" name="delCity"    value="<?php echo htmlspecialchars($addr['City']); ?>">
                    <input type="hidden" name="delCountry" value="<?php echo htmlspecialchars($addr['Country']); ?>">
                    <input type="hidden" name="delZip"     value="<?php echo htmlspecialchars($addr['ZipCode']); ?>">
                    <button type="submit" class="btn-delete" name="btnDeleteAddress"
                        onclick="return confirm('Delete this address?');">Delete</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p class="no-address">No addresses saved yet.</p>
    <?php endif; ?>

    <hr class="divider">

    <p class="add-address-title">➕ Add New Address</p>
    <form method="post">
        <div class="input-group">
            <span class="icon">🏙️</span>
            <input type="text" placeholder="City" name="txtCity" required maxlength="30">
        </div>
        <div class="input-group">
            <span class="icon">🌏</span>
            <input type="text" placeholder="Country" name="txtCountry" required maxlength="30">
        </div>
        <div class="input-group">
            <span class="icon">📮</span>
            <input type="text" placeholder="Zip Code" name="txtZipCode" required maxlength="10">
        </div>
        <button type="submit" class="login-btn" name="btnSaveAddress">Save Address</button>
    </form>
    <a href="/OnlineMarketplace/users/edit-profile.php" class="back-link">← Go Back</a>
</div>
</body>
</html>