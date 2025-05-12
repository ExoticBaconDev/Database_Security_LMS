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

// Check if ID is set
if (!isset($_GET['id']) && !isset($_POST['id'])) {
    die("Book ID is required.");
}

$bookID = $_GET['id'] ?? $_POST['id'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST['title'] ?? '';
    $author = $_POST['author'] ?? '';
    $isbn = $_POST['isbn'] ?? '';
    $categoryID = $_POST['category'] ?? '';

    // Check for duplicate ISBNs in other books
    $checkSql = "SELECT COUNT(*) AS Count FROM Books WHERE ISBN = ? AND BookID != ?";
    $checkStmt = sqlsrv_query($conn, $checkSql, array($isbn, $bookID));
    $row = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);

    if ($row['Count'] > 0) {
        $error = "Another book with the same ISBN already exists.";
    } else {
        // Update book
        $updateSql = "UPDATE Books SET Title = ?, Author = ?, ISBN = ?, CategoryID = ? WHERE BookID = ?";
        $params = array($title, $author, $isbn, $categoryID, $bookID);
        $updateStmt = sqlsrv_query($conn, $updateSql, $params);

        if ($updateStmt) {
            header("Location: managebooks.php");
            exit;
        } else {
            $error = print_r(sqlsrv_errors(), true);
        }
    }
}

// Fetch existing book details
$sql = "SELECT b.BookID, b.Title, b.Author, b.ISBN, b.CategoryID
        FROM Books b WHERE b.BookID = ?";
$stmt = sqlsrv_query($conn, $sql, array($bookID));
$book = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

// Fetch all categories for the dropdown
$categories = [];
$catQuery = "SELECT CategoryID, CategoryName FROM Categories";
$catStmt = sqlsrv_query($conn, $catQuery);
while ($row = sqlsrv_fetch_array($catStmt, SQLSRV_FETCH_ASSOC)) {
    $categories[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Book</title>
</head>
<body>
    <h1>Edit Book</h1>

    <?php if (isset($error)) echo "<p style='color:red;'>Error: $error</p>"; ?>

    <form method="POST">
        <input type="hidden" name="id" value="<?= $book['BookID'] ?>">
        <label>Title:</label><input type="text" name="title" value="<?= htmlspecialchars($book['Title']) ?>" required><br>
        <label>Author:</label><input type="text" name="author" value="<?= htmlspecialchars($book['Author']) ?>" required><br>
        <label>ISBN:</label><input type="text" name="isbn" value="<?= htmlspecialchars($book['ISBN']) ?>" required><br>
        <label>Category:</label>
        <select name="category" required>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['CategoryID'] ?>" <?= $cat['CategoryID'] == $book['CategoryID'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['CategoryName']) ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>
        <button type="submit" name="save">Save</button>
        <button type="button" onclick="window.location.href='managebooks.php'">Back</button>
    </form>
</body>
</html>

<?php sqlsrv_close($conn); ?>
