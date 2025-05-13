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
$sql = "SELECT u.Name, u.Email, p.Address, p.Phone, p.Age, p.Country
        FROM Users u
        LEFT JOIN Profiles p ON u.UserID = p.UserID
        WHERE u.UserID = ?";

$params = array($userId);
$stmt = sqlsrv_query($conn, $sql, $params);

// Begin HTML output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Profile</title>
    <link rel="stylesheet" href="../css/viewprofile.css">
</head>
<body>
    <main>
        <?php
        if ($stmt && ($profile = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))) {
            echo "<h1>Your Profile</h1>";
            echo "<p><strong>Name:</strong> " . htmlspecialchars($profile['Name']) . "</p>";
            echo "<p><strong>Email:</strong> " . htmlspecialchars($profile['Email']) . "</p>";
            echo "<p><strong>Address:</strong> " . htmlspecialchars($profile['Address']) . "</p>";
            echo "<p><strong>Phone:</strong> " . htmlspecialchars($profile['Phone']) . "</p>";
            echo "<p><strong>Age:</strong> " . htmlspecialchars($profile['Age']) . "</p>";
            echo "<p><strong>Country:</strong> " . htmlspecialchars($profile['Country']) . "</p>";

            echo '<form method="GET" action="editprofile.php">';
            echo '<input type="hidden" name="id" value="' . htmlspecialchars($userId) . '">';
            echo '<button type="submit">Edit Profile</button>';
            echo '<a href="dashboard.php"><button type="button">Back to Dashboard</button></a>';
            echo '</form>';
        } else {
            echo "<p>Profile not found.</p>";
        }
        ?>
    </main>
</body>
</html>

<?php
sqlsrv_close($conn);
?>
