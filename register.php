<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form inputs
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // hash for security
    $role = 'Member';

    // SQL Server connection info
    $serverName = "WINSVR2019"; // or your server name
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

    // Prepare and execute the insert query
    $sql = "INSERT INTO Users (Name, Email, Password, Role) VALUES (?, ?, ?, ?)";
    $params = array($name, $email, $password, $role);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt) {
        echo "<p>Registration successful! You can now log in.</p>";
        header("Location: login.php"); exit;
    } else {
        echo "<p>Registration failed: " . print_r(sqlsrv_errors(), true) . "</p>";
    }

    sqlsrv_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
</head>
<body>
    <h1>Register as an Account</h1>
    <form method="POST" action="">
        <label>Name:</label><input type="text" name="name" required><br>
        <label>Email:</label><input type="email" name="email" required><br>
        <label>Password:</label><input type="password" name="password" required><br>
        <button type="submit">Register</button>
        <a href="login.php"><button type="button">Login</button></a>
    </form>
</body>
</html>

