<?php
session_start();
if (!isset($_SESSION['user_name']) || $_SESSION['user_role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <h1>Admin Dashboard</h1>
    <ul>
        <li><a href="manageusers.php">Manage Users</a></li>
        <li><a href="backup.php">Backup Database</a></li>
    </ul>
    <a href="../login.php"><button type="button">Logout</button></a>
</body>
</html>
