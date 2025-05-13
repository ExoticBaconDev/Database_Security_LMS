<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<p>You must be logged in to view this page.</p>";
    exit;
}

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

// Get user ID
$userId = $_SESSION['user_id'];

// Fetch borrowed books
$sql = "
SELECT 
    b.Title, b.Author, b.ISBN,
    c.CategoryName,
    t.BorrowDate, t.ReturnDate
FROM 
    Transactions t
JOIN 
    Books b ON t.BookID = b.BookID
LEFT JOIN
    Categories c ON b.CategoryID = c.CategoryID
WHERE 
    t.UserID = ? AND t.ReturnDate IS NULL
";

$params = array($userId);
$stmt = sqlsrv_query($conn, $sql, $params);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Borrowed Books</title>
</head>
<body>
    <h1>My Borrowed Books</h1>
    <?php if ($stmt && sqlsrv_has_rows($stmt)): ?>
        <table border="1">
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>ISBN</th>
                <th>Category</th>
                <th>Borrow Date</th>
            </tr>
            <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['Title']); ?></td>
                    <td><?php echo htmlspecialchars($row['Author']); ?></td>
                    <td><?php echo htmlspecialchars($row['ISBN']); ?></td>
                    <td><?php echo htmlspecialchars($row['CategoryName']); ?></td>
                    <td><?php echo $row['BorrowDate']->format('Y-m-d'); ?></td>
                </tr>
            <?php endwhile; ?>
    </table>

    <?php else: ?>
        <p>You haven't borrowed any books yet.</p>
    <?php endif; ?>

    <a href="dashboard.php"><button type="button">Back</button></a>
</body>
</html>

<?php sqlsrv_close($conn); ?>

