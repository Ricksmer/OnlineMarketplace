<?php
session_start();
if(!isset($_SESSION['uname'])){
    header("Location:login.php");
    exit();
}
 
if($_SESSION['type'] != 1){
    die("Access denied. Only teachers can add events.");
}
 
$con      = mysqli_connect("127.0.0.1:3307","root","","cabatingan_eventmanagement") or die("Connection Error");
$username = $_SESSION['uname'];
$msg      = "";
 
if(isset($_POST['btnSave'])){
    $eventName       = mysqli_real_escape_string($con, $_POST['txtEventName']);
    $eventDate       = mysqli_real_escape_string($con, $_POST['txtEventDate']);
    $maxParticipants = (int)$_POST['txtMaxParticipants'];
    $roomID          = mysqli_real_escape_string($con, $_POST['txtRoomID']);

    $sql = "select * from event where eventDate=?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $eventDate);
    $stmt->execute();
    $result=$stmt->get_result();
    $row=mysqli_num_rows($result);
    $msg = "";
    
    if($row >= 1){
        while($events = mysqli_fetch_array($result)){
            if($events['eventName'] == $eventName){
                $msg = "<span style='color:red'>Invalid! Event already exists.</span>";
                break;
            }
            if($events['roomID'] == $roomID){
                $msg = "<span style='color:red'>Invalid! Room unavailable.</span>";
                break;
            }
        }
    } else {
        $sql  = "INSERT INTO event (eventName, eventDate, maxParticipants, roomID, username) VALUES (?, ?, ?, ?, ?)";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("ssiss", $eventName, $eventDate, $maxParticipants, $roomID, $username);
 
        if($stmt->execute()){
            $msg = "<span style='color:green'>Event saved successfully!</span>";
        } else {
            $msg = "<span style='color:red'>Error: " . $con->error . "</span>";
        }
    }
}

 
$rooms = mysqli_query($con, "SELECT roomID, roomName FROM room");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Event</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="center-page">
    <div class="card">
        <h2>Add Event</h2>
        <form method="post">
        <div class="wrapper">
            <div class="username">
                <?php echo htmlspecialchars($username); ?>
            </div>
        </div>
        <input type="text" name="txtEventName" placeholder="Event Name" required><br><br>
        <input type="date" name="txtEventDate" required><br><br>
        <input type="number" name="txtMaxParticipants" placeholder="Max Participants" min="1" required><br><br>
        <select name="txtRoomID" required>
        <option value="">-- Select Room --</option>
        <?php while($room = mysqli_fetch_array($rooms)): ?>
        <option value="<?php echo $room['roomID']; ?>">
            <?php echo $room['roomID'] . " - " . $room['roomName']; ?>
        </option>
        <?php endwhile; ?>
        </select><br><br>
            <button type="submit" name="btnSave" class="login-btn">Save Event</button>
        </form>
        <div class = "message">
            <?php echo $msg?>
        </div>
    </div>
</body>
<style>

</style>
</html>
