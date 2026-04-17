<?php
  session_start();

  $con = mysqli_connect("127.0.0.1","root","","online_marketplace") or die("Connection Error");
  $username = $_SESSION['uname'];
  $msg = "";

  $categories = mysqli_query($con, "SELECT CategoryName FROM category");

  
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="center-page">

    <div class="card form-card">

        <h2>Add Product</h2>

        <form method="post" class="form">

            <input type="text" name="txtEventName" placeholder="Product Name" required>
            <input type="number" name="txtMaxParticipants" placeholder="Price" min="1" required>
            <input type="number" name="txtMaxParticipants" placeholder="Stock / Quantity" min="1" required>

            <select name="txtRoomID" required>
              <?php while($category = mysqli_fetch_array($categories)): ?>
                <option value="<?php echo $category['CategoryName']; ?>">
                    <?php echo $category['CategoryName']; ?>
                </option>
              <?php endwhile; ?>
            </select>
            <input type="text" name="txtEventName" placeholder="Description" required>


            <button type="submit" name="btnSave" class="login-btn">
                Save Product
            </button>

        </form>

        <div class="message">
            <?php echo $msg; ?>
        </div>

    </div>

</body>

</html> 

<style>
  .form-card {
    width:720px;
    padding: 45px;
    border-radius: 18px;
    background: #ffffff;
    box-shadow: 0 25px 50px rgba(0,0,0,0.12);
  }

  .form-card h2 {
      margin-bottom: 25px;
      font-size: 22px;
      color: #1e1e1e;
  }

  .form {
      display: flex;
      flex-direction: column;
      gap: 14px;
  }

  /* Inputs */
  .form input,
  .form select {
      width: 100%;
      padding: 12px 14px;
      border-radius: 10px;
      border: 1px solid #ddd;
      font-size: 14px;
      outline: none;
      transition: 0.2s;
      background: #fafafa;
  }

  .form input:focus,
  .form select:focus {
      border-color: #1e6df6;
      background: #fff;
  }

  /* Button spacing fix */
  .form .login-btn {
      margin-top: 10px;
  }

  /* Message styling */
  .message {
      margin-top: 12px;
      font-size: 13px;
  }
  </style>