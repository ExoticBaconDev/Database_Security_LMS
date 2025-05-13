<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form inputs
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure hash
    $role = 'Member';

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

    // Insert user and get inserted UserID
    $sql = "INSERT INTO Users (Name, Email, Password, Role) 
            OUTPUT INSERTED.UserID 
            VALUES (?, ?, ?, ?)";
    $params = array($name, $email, $password, $role);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt && sqlsrv_fetch($stmt)) {
        $userID = sqlsrv_get_field($stmt, 0);
        // Redirect to additional profile info page
        header("Location: additional.php?user_id=" . urlencode($userID));
        exit;
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
    <h1>Register an Account</h1>
    <form method="POST" action="">
        <label>Name:</label><input type="text" name="name" required><br>
        <label>Email:</label><input type="email" name="email" required><br>
        <label>Password:</label><input type="password" name="password" required><br>
        <button type="submit">Register</button>
    </form>
</body>
</html>


