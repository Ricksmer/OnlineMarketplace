<?php
    session_start();
    $con = mysqli_connect("127.0.0.1","root","","online_marketplace") or die("Error in connection.");
    $str = "";

    if(isset($_POST['btnRegister'])){
        $uname = trim($_POST['txtUsername']);
        $pwd   = trim($_POST['txtPassword']);
        $cpwd  = trim($_POST['txtConfirmPassword']);
        $role  = trim($_POST['txtRole']);

        if($pwd !== $cpwd){
            $str = "Passwords do not match.";
        } elseif(empty($uname) || empty($pwd) || empty($role)){
            $str = "All fields are required.";
        } else {
            // Check if username already exists
            $check = $con->prepare("SELECT * FROM `user` WHERE username=?");
            $check->bind_param("s", $uname);
            $check->execute();
            
            if($check->get_result()->num_rows > 0){
                $str = "Username already taken.";
            } else {
                // Get the next UserID manually
                $maxId = $con->prepare("SELECT MAX(UserID) as maxId FROM `user`");
                $maxId->execute();
                $maxRow = $maxId->get_result()->fetch_assoc();
                $userId = ($maxRow['maxId'] ?? 0) + 1;

                // Insert into user table with explicit UserID
                $stmt = $con->prepare("INSERT INTO `user` (UserID, username, password) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $userId, $uname, $pwd);
                
                if(!$stmt->execute()){
                    $str = "Registration failed: " . $stmt->error;
                } else {
                    // Insert into role table
                    if($role === 'seller'){
                        $stmt2 = $con->prepare("INSERT INTO seller (UserID) VALUES (?)");
                        $stmt2->bind_param("i", $userId);
                        $stmt2->execute();
                    } else {
                        $stmt2 = $con->prepare("INSERT INTO buyer (UserID) VALUES (?)");
                        $stmt2->bind_param("i", $userId);
                        $stmt2->execute();
                    }

                    header("Location: login.php?registered=1");
                    exit();
                }
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .role-select {
            border: none;
            outline: none;
            width: 100%;
            font-size: 16px;
            background: transparent;
            cursor: pointer;
            color: #727272;
        }

        .role-select.selected {
            color: #333;
        }

        .role-select option {
            color: #333;
        }
    </style>
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

    <div class="input-group">
        <span class="icon"></span>
        <select name="txtRole" required class="role-select" id="txtRole">
            <option value="" disabled selected hidden>Purpose of Account</option>
            <option value="buyer">I want to buy products</option>
            <option value="seller">I want to sell products</option>
        </select>
    </div>

    <div class="error-output">
        <?php echo $str; ?>
    </div>

    <button type="submit" class="login-btn" name="btnRegister">Register</button>

    <p style="font-size: 16px; justify-self: center;" class="register-link">Already have an account? <a href="login.php">Log in here</a>.</p>
</form>
</div>

<script>
    const select = document.getElementById('txtRole');
    select.addEventListener('change', function () {
        this.classList.add('selected');
    });
</script>

</body>
</html>