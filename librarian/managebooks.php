<?php
// SQL Server connection
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

// Handle deletion
if (isset($_GET['delete'])) {
    $bookID = $_GET['delete'];
    $deleteQuery = "DELETE FROM Books WHERE BookID = ?";
    $deleteStmt = sqlsrv_query($conn, $deleteQuery, array($bookID));
    header("Location: managebooks.php");
    exit;
}

// Search logic
$searchTerm = $_GET['search'] ?? '';
$params = [];
$sql = "SELECT b.BookID, b.Title, b.Author, b.ISBN, c.CategoryName
        FROM Books b
        LEFT JOIN Categories c ON b.CategoryID = c.CategoryID
        WHERE b.Available = 1";

if (!empty($searchTerm)) {
    $sql .= " AND (b.Title LIKE ? OR b.Author LIKE ?)";
    $params[] = "%$searchTerm%";
    $params[] = "%$searchTerm%";
}

$stmt = sqlsrv_query($conn, $sql, $params);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Books</title>
    <style>
        table, th, td { border: 1px solid black; border-collapse: collapse; padding: 8px; }
        table { width: 100%; }
        th { background-color: #f2f2f2; }
        .add-button {
            float: right;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <h1>Manage Books</h1>
    <form method="GET" style="margin-bottom: 10px;">
        <input type="text" name="search" placeholder="Search title or author" value="<?= htmlspecialchars($searchTerm) ?>">
        <button type="submit">Search</button>
    </form>

    <a class="add-button" href="addbook.php"><button>Add Book</button></a>

    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>ISBN</th>
                <th>Category</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($stmt && sqlsrv_has_rows($stmt)): ?>
                <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['Title']) ?></td>
                        <td><?= htmlspecialchars($row['Author']) ?></td>
                        <td><?= htmlspecialchars($row['ISBN']) ?></td>
                        <td><?= htmlspecialchars($row['CategoryName'] ?? 'Uncategorized') ?></td>
                        <td>
                            <a href="editbook.php?id=<?= $row['BookID'] ?>"><button>Edit</button></a>
                            <button onclick="confirmDelete(<?= $row['BookID'] ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5">No books found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
        function confirmDelete(bookID) {
            if (confirm("Are you sure you want to delete this book?")) {
                window.location.href = "managebooks.php?delete=" + bookID;
            }
        }
    </script>
</body>
</html>

<?php sqlsrv_close($conn); ?>

