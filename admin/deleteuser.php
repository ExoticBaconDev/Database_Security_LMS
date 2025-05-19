<?php
session_start();

// Ensure only Admins can delete users
if (!isset($_SESSION['user_name']) || $_SESSION['user_role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

// Validate user ID from GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid user ID.");
}

$userId = intval($_GET['id']);

// Optional: Prevent an admin from deleting their own account
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId) {
    die("You cannot delete your own account.");
}

// Connect to SQL Server
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

// Delete the user securely
$sql = "DELETE FROM Users WHERE UserID = ?";
$params = array($userId);
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Close connection
sqlsrv_close($conn);

// Redirect back to the manage users page
header("Location: manageusers.php");
exit();
?>
