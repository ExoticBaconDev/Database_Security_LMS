<?php
session_start();

// SQL Server connection
$serverName = "WINSVR2019";
$connectionOptions = array(
    "Database" => "LibraryDB",
    "Uid" => "php_user",
    "PWD" => 'Pa$$w0rd'
);
$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Get user ID from session or GET
$userID = $_SESSION['user_id'] ?? $_GET['user_id'] ?? null;
if (!$userID) {
    die("User ID is required to continue.");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $address = $_POST['address'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $age = $_POST['age'] ?? '';
    $country = $_POST['country'] ?? '';

    $sql = "INSERT INTO Profiles (UserID, Address, Phone, Age, Country)
            VALUES (?, ?, ?, ?, ?)";
    $params = array($userID, $address, $phone, $age, $country);

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt) {
        // Clear session if needed
        unset($_SESSION['user_id']);
        header("Location: login.php");
        exit;
    } else {
        $error = print_r(sqlsrv_errors(), true);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Additional Information</title>
</head>
<body>
    <h1>Provide Additional Information</h1>
    <?php if (isset($error)) echo "<p style='color:red;'>Error: $error</p>"; ?>

    <form method="POST">
        <label>Address:</label><br>
        <input type="text" name="address" required><br><br>

        <label>Phone Number:</label><br>
        <input type="text" name="phone" required><br><br>

        <label>Age:</label><br>
        <input type="number" name="age" min="1" required><br><br>

        <label>Country:</label><br>
        <input type="text" name="country" required><br><br>

        <button type="submit">Save</button>
    </form>
</body>
</html>

<?php sqlsrv_close($conn); ?>
