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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST['title'] ?? '';
    $author = $_POST['author'] ?? '';
    $isbn = $_POST['isbn'] ?? '';
    $categoryID = $_POST['category'] ?? '';

    // Check for existing ISBN
    $checkSql = "SELECT COUNT(*) AS Count FROM Books WHERE ISBN = ?";
    $checkStmt = sqlsrv_query($conn, $checkSql, array($isbn));
    $row = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);

    if ($row['Count'] > 0) {
        $error = "A book with the same ISBN already exists.";
    } else {
        // Insert new book
        $insertSql = "INSERT INTO Books (Title, Author, ISBN, CategoryID) VALUES (?, ?, ?, ?)";
        $params = array($title, $author, $isbn, $categoryID);
        $insertStmt = sqlsrv_query($conn, $insertSql, $params);

        if ($insertStmt) {
            header("Location: managebooks.php");
            exit;
        } else {
            $error = print_r(sqlsrv_errors(), true);
        }
    }
}

// Fetch categories for dropdown
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
    <title>Add Book</title>
</head>
<body>
    <h1>Add Book</h1>

    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <form method="POST">
        <label>Title:</label><input type="text" name="title" required><br>
        <label>Author:</label><input type="text" name="author" required><br>
        <label>ISBN:</label><input type="text" name="isbn" required><br>
        <label>Category:</label>
        <select name="category" required>
            <option value="">-- Select Category --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['CategoryID'] ?>"><?= htmlspecialchars($cat['CategoryName']) ?></option>
            <?php endforeach; ?>
        </select><br><br>
        <button type="submit">Upload</button>
        <button type="button" onclick="window.location.href='managebooks.php'">Back</button>
    </form>
</body>
</html>

<?php sqlsrv_close($conn); ?>
