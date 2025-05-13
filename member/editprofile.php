<?php
session_start();
if (!isset($_SESSION['UserID']) || $_SESSION['user_role'] !== 'Member') {
    header("Location: ../login.php");
    exit;
}

$serverName = "WINSVR2019";
$connectionOptions = array(
    "Database" => "LibraryDB",
    "Uid" => "php_user",
    "PWD" => 'Pa$$w0rd'
);
$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    die("<p>Connection failed: " . print_r(sqlsrv_errors(), true) . "</p>");
}

$userId = $_SESSION['UserID'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Save updated profile
    $address = $_POST['address'];
    $phone   = $_POST['phone'];
    $age     = $_POST['age'];
    $country = $_POST['country'];

    // Check if profile already exists
    $checkSql = "SELECT * FROM Profiles WHERE UserID = ?";
    $checkStmt = sqlsrv_query($conn, $checkSql, array($userId));

    if ($checkStmt && sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC)) {
        // Update existing profile
        $updateSql = "UPDATE Profiles SET Address = ?, Phone = ?, Age = ?, Country = ? WHERE UserID = ?";
        $params = array($address, $phone, $age, $country, $userId);
    } else {
        // Insert new profile
        $updateSql = "INSERT INTO Profiles (Address, Phone, Age, Country, UserID) VALUES (?, ?, ?, ?, ?)";
        $params = array($address, $phone, $age, $country, $userId);
    }

    $stmt = sqlsrv_query($conn, $updateSql, $params);
    if ($stmt) {
        header("Location: viewprofile.php");
        exit;
    } else {
        echo "<p>Error updating profile: " . print_r(sqlsrv_errors(), true) . "</p>";
    }
}

// Get current profile info for the form
$sql = "SELECT Address, Phone, Age, Country FROM Profiles WHERE UserID = ?";
$stmt = sqlsrv_query($conn, $sql, array($userId));
if ($stmt === false) {
    die("<p>Error retrieving profile: " . print_r(sqlsrv_errors(), true) . "</p>");
}
$profile = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="../css/editprofile.css">
</head>
<body>
    <form method="POST" action="">
        <h1>Edit Your Profile</h1>
        <label>Address:</label><br>
        <input type="text" name="address" value="<?= htmlspecialchars($profile['Address'] ?? '') ?>" required><br>

        <label>Phone:</label><br>
        <input type="text" name="phone" value="<?= htmlspecialchars($profile['Phone'] ?? '') ?>" required><br>

        <label>Age:</label><br>
        <input type="number" name="age" value="<?= htmlspecialchars($profile['Age'] ?? '') ?>" required><br>

        <label>Country:</label><br>
        <input type="text" name="country" value="<?= htmlspecialchars($profile['Country'] ?? '') ?>" required><br><br>

        <button type="submit">Save</button>
        <a href="viewprofile.php"><button type="button">Back</button></a>
    </form>
</body>
</html>

<?php sqlsrv_close($conn); ?>
