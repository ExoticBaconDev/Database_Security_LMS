<?php
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

// Handle search/filter
$search = $_GET['search'] ?? '';
$roleFilter = $_GET['role'] ?? '';

$sql = "SELECT * FROM Users WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (Name LIKE ? OR Email LIKE ?)";
    $searchTerm = '%' . $search . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if (!empty($roleFilter)) {
    $sql .= " AND Role = ?";
    $params[] = $roleFilter;
}

$stmt = sqlsrv_query($conn, $sql, $params);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link rel="stylesheet" href="../css/manageusers.css">
    <script>
        function confirmDelete(userId) {
            if (confirm("Are you sure you want to delete this user?")) {
                window.location.href = "deleteuser.php?id=" + userId;
            }
        }
    </script>
</head>
<body>
    <h1>Manage Users</h1>

    <form method="GET" style="margin-bottom: 15px;">
        <input type="text" name="search" placeholder="Search by name or email" value="<?= htmlspecialchars($search) ?>">
        <select name="role">
            <option value="">All Roles</option>
            <option value="Admin" <?= $roleFilter == "Admin" ? "selected" : "" ?>>Admin</option>
            <option value="User" <?= $roleFilter == "User" ? "selected" : "" ?>>User</option>
        </select>
        <button type="submit">Filter</button>
    </form>

    <table>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
        <tr>
            <td><?= htmlspecialchars($row['Name']) ?></td>
            <td><?= htmlspecialchars($row['Email']) ?></td>
            <td><?= htmlspecialchars($row['Role']) ?></td>
            <td>
                <a href="edituser.php?id=<?= $row['UserID'] ?>">Edit</a> |
                <a href="javascript:void(0);" onclick="confirmDelete(<?= $row['UserID'] ?>)">Remove</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>

<?php sqlsrv_close($conn); ?>

