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

    // Fetch user
    $stmtUser = $con->prepare("SELECT * FROM user WHERE UserID=?");
    $stmtUser->bind_param("i", $userId);
    $stmtUser->execute();
    $currentUser = $stmtUser->get_result()->fetch_assoc();

    // Check if buyer and get preferred shipping address
    $isBuyer = false;
$preferredAddress = null;

$stmtBuyer = $con->prepare("SELECT PreferredShippingAddress FROM buyer WHERE UserID=?");
if($stmtBuyer === false) die("Buyer query failed: " . $con->error);
$stmtBuyer->bind_param("i", $userId);
$stmtBuyer->execute();
$buyerRow = $stmtBuyer->get_result()->fetch_assoc();

if($buyerRow){
    $isBuyer = true;
    $preferred = $buyerRow['PreferredShippingAddress'];

    // Parse the stored "City,Country,ZipCode" string
    if(!empty($preferred)){
        $parts = explode(',', $preferred);
        if(count($parts) === 3){
            $preferredAddress = [
                'City'    => $parts[0],
                'Country' => $parts[1],
                'ZipCode' => $parts[2]
            ];
        }
    }
}

    if(isset($_POST['btnSaveProfile'])){
        $newUsername = mysqli_real_escape_string($con, trim($_POST['txtUsername']));
        $newEmail    = mysqli_real_escape_string($con, trim($_POST['txtEmail']));
        $newPassword = $_POST['txtPassword'];
        $confirmPwd  = $_POST['txtConfirmPassword'];

        if(empty($newUsername)){
            $str = "Username cannot be empty.";
        } elseif(strlen($newUsername) > 15){
            $str = "Username must be 15 characters or fewer.";
        } elseif(!empty($newEmail) && strlen($newEmail) > 60){
            $str = "Email must be 60 characters or fewer.";
        } elseif(!empty($newEmail) && !filter_var($newEmail, FILTER_VALIDATE_EMAIL)){
            $str = "Invalid email format.";
        } elseif(!empty($newPassword) && $newPassword !== $confirmPwd){
            $str = "Passwords do not match.";
        } elseif(!empty($newPassword) && strlen($newPassword) > 15){
            $str = "Password must be 15 characters or fewer.";
        } else {
            $checkUname = $con->prepare("SELECT * FROM user WHERE Username=? AND UserID != ?");
            $checkUname->bind_param("si", $newUsername, $userId);
            $checkUname->execute();
            if($checkUname->get_result()->num_rows > 0){
                $str = "Username already taken.";
            } else {
                if(!empty($newPassword)){
                    $stmtUpdate = $con->prepare("UPDATE user SET Username=?, Email=?, Password=? WHERE UserID=?");
                    $stmtUpdate->bind_param("sssi", $newUsername, $newEmail, $newPassword, $userId);
                } else {
                    $stmtUpdate = $con->prepare("UPDATE user SET Username=?, Email=? WHERE UserID=?");
                    $stmtUpdate->bind_param("ssi", $newUsername, $newEmail, $userId);
                }

                if($stmtUpdate->execute()){
                    $_SESSION['uname'] = $newUsername;
                    $success = "Profile updated successfully.";
                    $stmtRefresh = $con->prepare("SELECT * FROM user WHERE UserID=?");
                    $stmtRefresh->bind_param("i", $userId);
                    $stmtRefresh->execute();
                    $currentUser = $stmtRefresh->get_result()->fetch_assoc();
                } else {
                    $str = "Failed to update profile. Please try again.";
                }
            }
        }
    }

    $backLink = "/OnlineMarketplace/users/buyer/buyer-interface.php"; // default
    $stmtRole = $con->prepare("SELECT UserID FROM seller WHERE UserID=?");
    $stmtRole->bind_param("i", $userId);
    $stmtRole->execute();
    if($stmtRole->get_result()->num_rows > 0){
        $backLink = "/OnlineMarketplace/users/seller/seller-interface.php";
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="/OnlineMarketplace/style.css">
    <style>
        .edit-profile-card {
            background: white;
            padding: 40px;
            width: 30vw;
            border-radius: 18px;
            box-shadow: 0 25px 45px rgba(0,0,0,0.12);
        }
        .edit-profile-card h2 {
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
            justify-content: center;
            text-align: center;
            margin-top: 16px;
            color: green;
            margin-bottom: 20px;
            padding: 10px;
        }
        .field-label {
            font-size: 12px;
            color: #888;
            text-align: left;
            margin-bottom: 4px;
            padding-left: 4px;
        }
        .password-note {
            font-size: 12px;
            color: #aaa;
            text-align: left;
            margin-top: -14px;
            margin-bottom: 16px;
            padding-left: 4px;
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
        .divider {
            border: none;
            border-top: 1px solid #eee;
            margin: 16px 0;
        }
        .shipping-section {
            margin-bottom: 16px;
        }
        .shipping-label {
            font-size: 12px;
            color: #888;
            margin-bottom: 6px;
            padding-left: 4px;
        }
        .shipping-address-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f2f4f8;
            border: 1px solid #dde1f0;
            border-radius: 10px;
            padding: 12px 16px;
            width: 100%;
            text-align: left;
            cursor: pointer;
            font-size: 14px;
            color: #333;
            text-decoration: none;
            transition: background 0.2s;
        }
        .shipping-address-btn:hover {
            background: #e6eaf5;
        }
        .shipping-address-btn .arrow {
            margin-left: auto;
            color: #aaa;
            font-size: 16px;
        }
    </style>
</head>
<body class="center-page">
<div class="edit-profile-card">
    <h2 class="section-title">Edit Profile</h2>

    <form method="post">
        <p class="field-label">Username</p>
        <div class="input-group">
            <span class="icon">👤</span>
            <input type="text" placeholder="Username" name="txtUsername" required maxlength="15"
                value="<?php echo htmlspecialchars($currentUser['Username']); ?>">
        </div>

        <p class="field-label">Email</p>
        <div class="input-group">
            <span class="icon">✉️</span>
            <input type="email" placeholder="Email (optional)" name="txtEmail" maxlength="60"
                value="<?php echo htmlspecialchars($currentUser['Email'] ?? ''); ?>">
        </div>

        <hr class="divider">

        <p class="field-label">New Password</p>
        <div class="input-group">
            <span class="icon">🔒</span>
            <input type="password" placeholder="New Password" name="txtPassword" maxlength="15" id="txtPassword">
        </div>
        <p class="password-note">Leave blank to keep current password.</p>

        <p class="field-label">Confirm New Password</p>
        <div class="input-group">
            <span class="icon">🔒</span>
            <input type="password" placeholder="Confirm New Password" name="txtConfirmPassword" maxlength="15">
        </div>

        <!-- Preferred Shipping Address — buyers only -->
        <?php if($isBuyer): ?>
        <hr class="divider">
        <div class="shipping-section">
            <p class="shipping-label">🚚 Preferred Shipping Address</p>
            <a href="/OnlineMarketplace/users/manage-addresses.php" class="shipping-address-btn">
                <?php if(!empty($preferredAddress['City'])): ?>
                    <span>📍 <?php echo htmlspecialchars($preferredAddress['City']); ?>,
                    <?php echo htmlspecialchars($preferredAddress['Country']); ?> —
                    <?php echo htmlspecialchars($preferredAddress['ZipCode']); ?></span>
                <?php else: ?>
                    <span style="color:#aaa;">No preferred address set</span>
                <?php endif; ?>
                <span class="arrow">›</span>
            </a>
        </div>
        <?php endif; ?>

        <?php if(!empty($success)): ?>
        <div class="success-output"><?php echo $success; ?></div>
        <?php endif; ?>
        <div class="error-output"><?php echo $str; ?></div>

        <button type="submit" class="login-btn" name="btnSaveProfile">Save Changes</button>
    </form>

    <a href="<?php echo $backLink; ?>" class="back-link">← Go Back</a>
</div>
</body>
</html>