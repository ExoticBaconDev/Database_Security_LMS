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

// Fetch existing book details
$sql = "SELECT BookID, Title, Author, ISBN, CategoryID, PdfPath FROM Books WHERE BookID = ?";
$stmt = sqlsrv_query($conn, $sql, array($bookID));
$book = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

// Fetch all categories
$categories = [];
$catQuery = "SELECT CategoryID, CategoryName FROM Categories";
$catStmt = sqlsrv_query($conn, $catQuery);
while ($row = sqlsrv_fetch_array($catStmt, SQLSRV_FETCH_ASSOC)) {
    $categories[] = $row;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST['title'] ?? '';
    $author = $_POST['author'] ?? '';
    $isbn = $_POST['isbn'] ?? '';
    $categoryID = $_POST['category'] ?? '';
    $pdfPath = $book['PdfPath']; // default to existing

    // Check for duplicate ISBNs
    $checkSql = "SELECT COUNT(*) AS Count FROM Books WHERE ISBN = ? AND BookID != ?";
    $checkStmt = sqlsrv_query($conn, $checkSql, array($isbn, $bookID));
    $row = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);

    if ($row['Count'] > 0) {
        $error = "Another book with the same ISBN already exists.";
    } else {
        // Handle new file upload
        if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['pdf']['tmp_name'];
            $fileName = $_FILES['pdf']['name'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if ($fileExt === "pdf") {
                $newFileName = uniqid() . '_' . $fileName;
                $uploadDir = "../uploads/";
                $destPath = $uploadDir . $newFileName;

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    $pdfPath = $destPath;
                } else {
                    $error = "Failed to upload file.";
                }
            } else {
                $error = "Only PDF files are allowed.";
            }
        }

        if (!isset($error)) {
            // Update book with new info
            $updateSql = "UPDATE Books SET Title = ?, Author = ?, ISBN = ?, CategoryID = ?, PdfPath = ? WHERE BookID = ?";
            $params = array($title, $author, $isbn, $categoryID, $pdfPath, $bookID);
            $updateStmt = sqlsrv_query($conn, $updateSql, $params);

            if ($updateStmt) {
                header("Location: managebooks.php");
                exit;
            } else {
                $error = print_r(sqlsrv_errors(), true);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Book</title>
    <link rel="stylesheet" href="../css/editbook.css">
</head>
<body>
    <?php if (isset($error)) echo "<p style='color:red;'>Error: $error</p>"; ?>

    <form method="POST" enctype="multipart/form-data">
        <h1>Edit Book</h1>
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
        </select><br>
        <label>PDF File (optional):</label><input type="file" name="pdf" accept="application/pdf"><br>
        <?php if ($book['PdfPath']): ?>
            <p>Current file: <a href="<?= htmlspecialchars($book['PdfPath']) ?>" target="_blank">View PDF</a></p>
        <?php endif; ?>
        <br>
        <button type="submit">Save</button>
        <button type="button" onclick="window.location.href='managebooks.php'">Back</button>
    </form>
</body>
</html>

<?php sqlsrv_close($conn); ?>

