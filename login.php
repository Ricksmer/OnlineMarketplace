<?php
    session_start();
    $con = mysqli_connect("127.0.0.1","root","","online_marketplace") or die("Error in connection.");
    $str = "";

    // Show success message if coming from register
    $registered = isset($_GET['registered']) ? "Account created! Please log in." : "";

    if(isset($_POST['btnLogin'])){
        $uname = mysqli_real_escape_string($con, $_POST['txtUsername']);
        $pwd   = mysqli_real_escape_string($con, $_POST['txtPassword']);

        $sql  = "SELECT * FROM user WHERE username=? AND password=?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("ss", $uname, $pwd);
        $stmt->execute();
        $result = $stmt->get_result();
        $row    = mysqli_num_rows($result);

        if($row == 1){
            $val    = mysqli_fetch_array($result);
            $userId = $val['UserID'];

            $sql  = "SELECT * FROM seller WHERE UserID=?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $seller_result = $stmt->get_result();

            if(mysqli_num_rows($seller_result) == 1){
                $_SESSION['userId'] = $val['UserID'];
                $_SESSION['uname']  = $val['username'];
                session_regenerate_id(true);
                header("Location: /OnlineMarketplace/users/seller/seller-interface.php");
            } else {
                $_SESSION['userId'] = $val['UserID'];
								$_SESSION['uname']  = $val['username'];
								session_regenerate_id(true);
								header("Location: /OnlineMarketplace/users/buyer/buyer-interface.html");
            }
        } else {
            $str = "Invalid credentials";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="center-page">
<div class="login-card">
<h2>Login</h2>

<form method="post">
    <div class="input-group">
        <span class="icon"></span>
        <input type="text" placeholder="Username" required name="txtUsername">
    </div>

    <div class="input-group">
        <span class="icon"></span>
        <input type="password" placeholder="Password" required name="txtPassword">
    </div>

    <?php if(!empty($registered)): ?>
    <div class="error-output" style="color: green;">
        <?php echo $registered; ?>
    </div>
    <?php endif; ?>

    <div class="error-output">
        <?php echo $str; ?>
    </div>

    <button type="submit" class="login-btn" name="btnLogin">Log in</button>

    <p style="font-size: 16px; justify-self: center;"class="register-link">Don't have an account? <a href="register.php">Register here</a>.</p>
</form>
</div>
</body>
</html>