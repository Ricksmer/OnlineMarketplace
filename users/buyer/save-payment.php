<?php
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: ../../login.php");
    exit();
}
    if($_SESSION['role'] !== 'buyer'){
        header("Location: ../seller/seller-interface.php");
        exit();
    }

$con = mysqli_connect("127.0.0.1", "root", "", "online_marketplace") or die("Connection Error.");

$userId = $_SESSION['userId'];

if (isset($_POST['btnSavePayment'])) {

    $orderID = (int) $_POST['orderID'];
    $voucherID = $_POST['voucherID'] !== "" ? (int) $_POST['voucherID'] : null;
    $paymentAmount = (float) $_POST['paymentAmount'];
    $paymentMethod = trim($_POST['paymentMethod']);

    if ($paymentAmount < 0) {
        echo "<script>
            alert('Payment amount cannot be negative.');
            window.location='add-payment.php';
        </script>";
        exit();
    }

    /*
        Security check:
        Make sure the selected order belongs to the logged-in buyer.
    */
    $checkOrder = $con->prepare("
        SELECT OrderID, TotalAmount
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

    $order = $orderResult->fetch_assoc();
    $orderTotal = (float) $order['TotalAmount'];
    $discountAmount = 0;

    if ($voucherID !== null) {
        $voucherStmt = $con->prepare("SELECT DiscountAmount FROM voucher WHERE VoucherID = ?");
        $voucherStmt->bind_param("i", $voucherID);
        $voucherStmt->execute();
        $voucherRow = $voucherStmt->get_result()->fetch_assoc();

        if ($voucherRow) {
            $discountAmount = (float) $voucherRow['DiscountAmount'];
        }
    }

    $amountWithDiscount = $paymentAmount + $discountAmount;
    $paymentStatus = ($amountWithDiscount >= $orderTotal) ? "Paid" : "Pending";

    $maxPayment = $con->prepare("SELECT MAX(PaymentID) AS MaxPaymentID FROM payment");
    $maxPayment->execute();
    $maxPaymentRow = $maxPayment->get_result()->fetch_assoc();
    $paymentID = $maxPaymentRow['MaxPaymentID'] + 1;

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
