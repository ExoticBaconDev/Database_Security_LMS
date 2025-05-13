<?php
session_start(); // To store logged-in user info

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // SQL Server connection
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

    // Fetch user by email or username (depending on your schema)
    $sql = "SELECT UserID, Name, Email, Password, Role FROM Users WHERE Email = ? OR Name = ?";
    $params = array($username, $username);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt && sqlsrv_has_rows($stmt)) {
        $user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        if (password_verify($password, $user['Password'])) {
            // Password matched, start session
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['user_name'] = $user['Name'];
            $_SESSION['user_role'] = $user['Role'];

            echo "<p>Login successful. Welcome, " . htmlspecialchars($user['Name']) . " (" . $user['Role'] . ")</p>";
            // Redirect based on role

            if ($_SESSION['user_role'] == "Librarian") {
                 header("Location: librarian/dashboard.php"); exit;
            }
            else if ($_SESSION['user_role'] == "Member") {
                header("Location: member/dashboard.php"); exit;
            }
            
        } else {
            echo "<p>Invalid password.</p>";
        }
    } else {
        echo "<p>User not found.</p>";
    }

    sqlsrv_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <h1>Login</h1>
    <form method="POST" action="">
        <label>Username (Name or Email):</label>
        <input type="text" name="username" required><br>
        <label>Password:</label>
        <input type="password" name="password" required><br>
        <button type="submit">Login</button>
        <button type="button" onclick="window.location.href='register.php'">Login</button>
    </form>
</body>
</html>
