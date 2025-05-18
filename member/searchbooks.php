<?php
session_start();
if (!isset($_SESSION['user_name']) || $_SESSION['user_role'] !== 'Member') {
    header("Location: ../login.php");
    exit();
}

// SQL Server connection setup
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

// Fetch categories for dropdown
$categoryQuery = "SELECT CategoryID, CategoryName FROM Categories";
$categoryResult = sqlsrv_query($conn, $categoryQuery);

// Handle search input
$whereClauses = [];
$params = [];

if (!empty($_GET['query'])) {
    $whereClauses[] = "(b.Title LIKE ? OR b.Author LIKE ?)";
    $searchTerm = "%" . $_GET['query'] . "%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if (!empty($_GET['category'])) {
    $whereClauses[] = "b.CategoryID = ?";
    $params[] = $_GET['category'];
}

// Build search query
$sql = "SELECT b.Title, b.Author, b.ISBN, b.PdfPath, c.CategoryName
        FROM Books b
        LEFT JOIN Categories c ON b.CategoryID = c.CategoryID";

if (!empty($whereClauses)) {
    $sql .= " WHERE " . implode(" AND ", $whereClauses);
}

$stmt = sqlsrv_query($conn, $sql, $params);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Books</title>
    <link rel="stylesheet" href="../css/searchbooks.css">
</head>
<body>
    <a href="dashboard.php"><button type="button">Back</button></a>
    <h1>Search Books</h1>
    <form method="GET" action="">
        <input type="text" name="query" placeholder="Enter title or author" value="<?= htmlspecialchars($_GET['query'] ?? '') ?>">
        <select name="category">
            <option value="">-- All Categories --</option>
            <?php while ($row = sqlsrv_fetch_array($categoryResult, SQLSRV_FETCH_ASSOC)): ?>
                <option value="<?= $row['CategoryID'] ?>" 
                    <?= (isset($_GET['category']) && $_GET['category'] == $row['CategoryID']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['CategoryName']) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <button type="submit">Search</button>
    </form>

    <?php if ($stmt): ?>
        <h2>Search Results:</h2>
        <ul>
            <?php while ($book = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
                <li>
                    <strong><?= htmlspecialchars($book['Title']) ?></strong> by <?= htmlspecialchars($book['Author']) ?> |
                    ISBN: <?= htmlspecialchars($book['ISBN']) ?> |
                    Category: <?= htmlspecialchars($book['CategoryName'] ?? 'Uncategorized') ?>
                    <?php if (!empty($book['PdfPath'])): ?>
                        | <a href="<?= htmlspecialchars($book['PdfPath']) ?>" target="_blank">View PDF</a>
                    <?php endif; ?>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>No books found or query error.</p>
    <?php endif; ?>
</body>
</html>

<?php sqlsrv_close($conn); ?>


