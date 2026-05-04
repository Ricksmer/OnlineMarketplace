<?php
  $str="";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="center-page">
<div class="login-card">
<h2>Register</h2>

<form method="post">
    <div class="input-group">
        <span class="icon"></span>
        <input type="text" placeholder="Username" required name="txtUsername">
    </div>

    <div class="input-group">
        <span class="icon"></span>
        <input type="password" placeholder="Password" required name="txtPassword">
    </div>

    <div class="input-group">
        <span class="icon"></span>
        <input type="password" placeholder="Confirm Password" required name="txtConfirmPassword">
    </div>

    <div class="role-select-wrap">
        <label class="role-label">ROLE</label>
        <select class="role-select" name="txtRole" required>
            <option value="" disabled selected>Select a role</option>
            <option value="buyer">Buyer</option>
            <option value="seller">Seller</option>
        </select>
    </div>

    <div class="error-output">
        <?php echo $str; ?>
    </div>

    <button type="submit" class="login-btn" name="btnRegister">Register</button>

    <p class="register-link">Already have an account? <a href="login.php">Log in here</a>.</p>
</form>
</div>

</body>
</html>