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

   
    $stmtUser = $con->prepare("SELECT * FROM user WHERE UserID=?");
    $stmtUser->bind_param("i", $userId);
    $stmtUser->execute();
    $currentUser = $stmtUser->get_result()->fetch_assoc();
    if(isset($_POST['btnAddAddress'])){
        header("Location: add-address.php");
        exit();
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
        } elseif(!empty($newEmail) && strlen($newEmail) > 30){
            $str = "Email must be 30 characters or fewer.";
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
                // Build update query depending on whether password is being changed
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
                    // Refresh current user data
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
        .back-link:hover {
            text-decoration: underline;
        }
        .divider {
            border: none;
            border-top: 1px solid #eee;
            margin: 16px 0;
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
            <input
                type="text"
                placeholder="Username"
                name="txtUsername"
                required
                maxlength="15"
                value="<?php echo htmlspecialchars($currentUser['Username']); ?>"
            >
        </div>

        <p class="field-label">Email</p>
        <div class="input-group">
            <span class="icon">✉️</span>
            <input
                type="email"
                placeholder="Email (optional)"
                name="txtEmail"
                maxlength="30"
                value="<?php echo htmlspecialchars($currentUser['Email'] ?? ''); ?>"
            >
        </div>

        <hr class="divider">

        <p class="field-label">New Password</p>
        <div class="input-group">
            <span class="icon">🔒</span>
            <input
                type="password"
                placeholder="New Password"
                name="txtPassword"
                maxlength="15"
                id="txtPassword"
            >
        </div>
        <p class="password-note">Leave blank to keep current password.</p>

        <p class="field-label">Confirm New Password</p>
        <div class="input-group">
            <span class="icon">🔒</span>
            <input
                type="password"
                placeholder="Confirm New Password"
                name="txtConfirmPassword"
                maxlength="15"
                id="txtConfirmPassword"
            >
        </div>

        <?php if(!empty($success)): ?>
        <div class="success-output"><?php echo $success; ?></div>
        <?php endif; ?>
        <div class="error-output"><?php echo $str; ?></div>

        <button type="submit" class="login-btn" name="btnAddAddress">Temporary Button for Add Address</button>

        <button type="submit" class="login-btn" name="btnSaveProfile">Save Changes</button>
    </form>
    <a href="javascript:history.back()" class="back-link">← Go Back</a>
</div>
</body>
</html>