<?php
session_start(); // Make sure user session is active

// Normally, you store logged-in user info in session. Here we assume UserID is stored.
if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit;
}

$userID = $_SESSION['UserID'];

// SQL Server connection info
$serverName = "WINSVR2019";
$connectionOptions = array(
    "Database" => "LibraryDB",
    "Uid" => "php_user",
    "PWD" => 'Pa$$w0rd'
);

// Connect to SQL Server
$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Query to fetch user and profile info
$sql = "
    SELECT 
        u.Name, u.Email,
        p.Address, p.Phone, p.Age, p.Country
    FROM Users u
    LEFT JOIN Profiles p ON u.UserID = p.UserID
    WHERE u.UserID = ?
";
$params = array($userID);
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt && ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))) {
    $name = $row['Name'];
    $email = $row['Email'];
    $address = $row['Address'] ?? 'Not provided';
    $phone = $row['Phone'] ?? 'Not provided';
    $age = $row['Age'] ?? 'Not provided';
    $country = $row['Country'] ?? 'Not provided';
} else {
    die("Profile not found or query error.");
}

sqlsrv_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Profile</title>
</head>
<body>
    <h1>Your Profile</h1>
    <p><strong>Name:</strong> <?= htmlspecialchars($name) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
    <p><strong>Address:</strong> <?= htmlspecialchars($address) ?></p>
    <p><strong>Phone:</strong> <?= htmlspecialchars($phone) ?></p>
    <p><strong>Age:</strong> <?= htmlspecialchars($age) ?></p>
    <p><strong>Country:</strong> <?= htmlspecialchars($country) ?></p>

    <a href="dashboard.php"><button type="button">Back to Dashboard</button></a>
</body>
</html>
