<?php
	session_start();
	$con=mysqli_connect("127.0.0.1:3307","root","","cabatingan_eventmanagement") or die ("Error in connection.");
	$str="";
	if(isset($_POST['btnLogin'])){
		$uname=mysqli_real_escape_string($con,$_POST['txtUsername']);
		$pwd=mysqli_real_escape_string($con,$_POST['txtPassword']);
 
 
		$sql="select * from users where username=? and password=?";
		//echo $sql;
		$stmt=$con->prepare($sql);
		$stmt->bind_param("ss",$uname,$pwd);
		$stmt->execute();
		$result=$stmt->get_result();
		$row=mysqli_num_rows($result);
		if($row == 1){
		    $val = mysqli_fetch_array($result);
		    $_SESSION['uname'] = $val['username'];
		    $_SESSION['type']  = $val['type'];
		    header("Location:add-event.php");
		}
		else
			$str="Invalid credentials";
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
	    <button type="submit" class="login-btn" name="btnLogin">Log in</button>

	<div class = "error-output">
		<?php echo $str; ?>
	</div>
</form>
</div>
 
</body>
</html>
