<?php
session_start();
if (!isset($_SESSION['user_name']) || $_SESSION['user_role'] !== 'Member') {
    header("Location: ../login.php");
    exit();
}
?>
    
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Member Dashboard</title>
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <h1>Welcome, Member</h1>
    <ul>
        <li><a href="viewprofile.php">My Profile</a></li>
        <li><a href="searchbooks.php">Search Books</a></li>
    </ul>
    <a href="../login.php"><button type="button">Logout</button></a>
</body>
</html>
