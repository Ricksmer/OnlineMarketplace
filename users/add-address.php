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

  
    $checkAddr = $con->prepare("SELECT * FROM address WHERE UserID=?");
    $checkAddr->bind_param("i", $userId);
    $checkAddr->execute();
    $addrResult = $checkAddr->get_result();
    $existingAddress = $addrResult->fetch_assoc();

    if(isset($_POST['btnSaveAddress'])){
        $city    = mysqli_real_escape_string($con, trim($_POST['txtCity']));
        $country = mysqli_real_escape_string($con, trim($_POST['txtCountry']));
        $zip     = mysqli_real_escape_string($con, trim($_POST['txtZipCode']));

        if(empty($city) || empty($country) || empty($zip)){
            $str = "All fields are required.";
        } elseif(!is_numeric($zip)){
            $str = "Zip Code must be a number.";
        } else {
            if($existingAddress){
               
                $stmt = $con->prepare("UPDATE address SET City=?, Country=?, ZipCode=? WHERE UserID=?");
                $stmt->bind_param("ssii", $city, $country, $zip, $userId);
            } else {
                
                $stmt = $con->prepare("INSERT INTO address (UserID, City, Country, ZipCode) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("issi", $userId, $city, $country, $zip);
            }

            if($stmt->execute()){
                $success = "Address saved successfully.";
               
                $checkAddr2 = $con->prepare("SELECT * FROM address WHERE UserID=?");
                $checkAddr2->bind_param("i", $userId);
                $checkAddr2->execute();
                $existingAddress = $checkAddr2->get_result()->fetch_assoc();
            } else {
                $str = "Failed to save address. Please try again.";
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Address</title>
    <link rel="stylesheet" href="/OnlineMarketplace/style.css">
    <style>
        .section-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #0a4cd3;
            text-align: center;
        }
        .success-output {
            justify-content: center;
            text-align: center;
            margin-top: 16px;
            color: green;
            margin-bottom: 20px;
            padding: 10px;
        }
        .current-address {
            background: #f2f4f8;
            border-radius: 10px;
            padding: 14px 18px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #333;
            text-align: left;
        }
        .current-address span {
            font-weight: bold;
            color: #0a4cd3;
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
</head>
<body class="center-page">
<div class="login-card">
    <h2 class="section-title"><?php echo $existingAddress ? "Edit Address" : "Add Address"; ?></h2>

    <?php if($existingAddress): ?>
    <div class="current-address">
        <strong>Current Address:</strong><br>
        <?php echo htmlspecialchars($existingAddress['City']); ?>,
        <?php echo htmlspecialchars($existingAddress['Country']); ?> —
        <?php echo htmlspecialchars($existingAddress['ZipCode']); ?>
    </div>
    <?php endif; ?>

    <form method="post">
        <div class="input-group">
            <span class="icon">🏙️</span>
            <input
                type="text"
                placeholder="City"
                name="txtCity"
                required
                maxlength="30"
                value="<?php echo $existingAddress ? htmlspecialchars($existingAddress['City']) : ''; ?>"
            >
        </div>
        <div class="input-group">
            <span class="icon">🌏</span>
            <input
                type="text"
                placeholder="Country"
                name="txtCountry"
                required
                maxlength="30"
                value="<?php echo $existingAddress ? htmlspecialchars($existingAddress['Country']) : ''; ?>"
            >
        </div>
        <div class="input-group">
            <span class="icon">📮</span>
            <input
                type="text"
                placeholder="Zip Code"
                name="txtZipCode"
                required
                maxlength="10"
                value="<?php echo $existingAddress ? htmlspecialchars($existingAddress['ZipCode']) : ''; ?>"
            >
        </div>

        <?php if(!empty($success)): ?>
        <div class="success-output"><?php echo $success; ?></div>
        <?php endif; ?>
        <div class="error-output"><?php echo $str; ?></div>

        <button type="submit" class="login-btn" name="btnSaveAddress">
            <?php echo $existingAddress ? "Update Address" : "Save Address"; ?>
        </button>
    </form>
    <a href="javascript:history.back()" class="back-link">← Go Back</a>
</div>
</body>
</html>

