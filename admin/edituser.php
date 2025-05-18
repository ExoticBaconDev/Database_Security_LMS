<?php
session_start();
if (!isset($_SESSION['user_name']) || $_SESSION['user_role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
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

$userId = $_GET['id'] ?? null;
if (!$userId) {
    die("Invalid user ID.");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $role = $_POST["role"];
    $password = $_POST["password"];

    // Check for duplicate email (excluding current user)
    $checkSql = "SELECT * FROM Users WHERE Email = ? AND UserID != ?";
    $checkStmt = sqlsrv_query($conn, $checkSql, [$email, $userId]);
    if (sqlsrv_has_rows($checkStmt)) {
        $error = "Email already exists.";
    } else {
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $updateSql = "UPDATE Users SET Name = ?, Email = ?, Password = ?, Role = ? WHERE UserID = ?";
            $params = [$name, $email, $hashedPassword, $role, $userId];
        } else {
            $updateSql = "UPDATE Users SET Name = ?, Email = ?, Role = ? WHERE UserID = ?";
            $params = [$name, $email, $role, $userId];
        }

        $updateStmt = sqlsrv_query($conn, $updateSql, $params);
        if ($updateStmt) {
            header("Location: manageusers.php");
            exit;
        } else {
            $error = "Failed to update user.";
        }
    }
}

// Fetch current user data
$sql = "SELECT * FROM Users WHERE UserID = ?";
$stmt = sqlsrv_query($conn, $sql, [$userId]);
$user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <link rel="stylesheet" href="../css/edituser.css">
</head>
<body>
    <h1>Edit User</h1>

    <?php if (!empty($error)): ?>
        <p style="color:red"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Name:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user['Name']) ?>" required><br>

        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['Email']) ?>" required><br>

        <label>New Password:</label>
        <input type="password" name="password" placeholder="Leave blank to keep existing"><br>

        <label>Role:</label>
        <select name="role" required>
            <option value="Admin" <?= $user['Role'] === 'Admin' ? 'selected' : '' ?>>Admin</option>
            <option value="User" <?= $user['Role'] === 'User' ? 'selected' : '' ?>>User</option>
        </select><br>

        <button type="submit">Save</button>
        <button type="button" onclick="window.location.href='manageusers.php'">Back</button>
    </form>
</body>
</html>

<?php sqlsrv_close($conn); ?>
