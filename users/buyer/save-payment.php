<?php
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: ../../login.php");
    exit();
}

$con = mysqli_connect("127.0.0.1", "root", "", "online_marketplace") or die("Connection Error.");

$userId = $_SESSION['userId'];

if (isset($_POST['btnSavePayment'])) {

    $paymentID = (int) $_POST['paymentID'];
    $orderID = (int) $_POST['orderID'];
    $voucherID = $_POST['voucherID'] !== "" ? (int) $_POST['voucherID'] : null;
    $paymentAmount = (float) $_POST['paymentAmount'];
    $paymentMethod = trim($_POST['paymentMethod']);
    $paymentStatus = trim($_POST['paymentStatus']);

    /*
        Security check:
        Make sure the selected order belongs to the logged-in buyer.
    */
    $checkOrder = $con->prepare("
        SELECT OrderID 
        FROM `order` 
        WHERE OrderID = ? AND BuyerID = ?
    ");
    $checkOrder->bind_param("ii", $orderID, $userId);
    $checkOrder->execute();
    $orderResult = $checkOrder->get_result();

    if ($orderResult->num_rows === 0) {
        echo "<script>
            alert('Invalid order selected.');
            window.location='add-payment.php';
        </script>";
        exit();
    }

    if ($voucherID === null) {
        $sql = "
            INSERT INTO payment 
            (PaymentID, OrderID, VoucherID, PaymentAmount, PaymentMethod, Status)
            VALUES (?, ?, NULL, ?, ?, ?)
        ";

        $stmt = $con->prepare($sql);
        $stmt->bind_param(
            "iidss",
            $paymentID,
            $orderID,
            $paymentAmount,
            $paymentMethod,
            $paymentStatus
        );
    } else {
        $sql = "
            INSERT INTO payment 
            (PaymentID, OrderID, VoucherID, PaymentAmount, PaymentMethod, Status)
            VALUES (?, ?, ?, ?, ?, ?)
        ";

        $stmt = $con->prepare($sql);
        $stmt->bind_param(
            "iiidss",
            $paymentID,
            $orderID,
            $voucherID,
            $paymentAmount,
            $paymentMethod,
            $paymentStatus
        );
    }

    if ($stmt->execute()) {
        echo "<script>
            alert('Payment recorded successfully.');
            window.location='buyer-interface.php';
        </script>";
    } else {
        echo "<script>
            alert('Error saving payment: " . addslashes($stmt->error) . "');
            window.location='add-payment.php';
        </script>";
    }

    $stmt->close();

} else {
    header("Location: add-payment.php");
    exit();
}
?>