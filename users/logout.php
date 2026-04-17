<?php
  session_start();
  session_destroy();
  header("Location: /OnlineMarketplace/login.php");
  exit();
?>