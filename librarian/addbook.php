<?php
$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $isbn = trim($_POST['isbn']);
    $categoryID = intval($_POST['category']);

    // File upload variables
    $pdfFile = $_FILES['pdf'];
    $uploadDir = "uploads/";
    $pdfPath = null;

    // Connect to database
    $conn = sqlsrv_connect("WINSVR2019", [
        "Database" => "LibraryDB",
        "Uid" => "php_user",
        "PWD" => 'Pa$$w0rd'
    ]);

    if (!$conn) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Check for duplicate ISBN
    $checkQuery = "SELECT 1 FROM Books WHERE ISBN = ?";
    $checkStmt = sqlsrv_query($conn, $checkQuery, [$isbn]);

    if (sqlsrv_has_rows($checkStmt)) {
        $message = "ISBN already exists.";
    } else {
        // Validate and move uploaded PDF
        if ($pdfFile["error"] == 0 && strtolower(pathinfo($pdfFile["name"], PATHINFO_EXTENSION)) == "pdf") {
            $uniqueName = uniqid() . "_" . basename($pdfFile["name"]);
            $targetPath = $uploadDir . $uniqueName;

            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            if (move_uploaded_file($pdfFile["tmp_name"], $targetPath)) {
                $pdfPath = $targetPath;
            } else {
                $message = "Failed to upload PDF file.";
            }
        } elseif ($pdfFile["error"] != 4) {
            $message = "Invalid file format. Only PDFs are allowed.";
        }

        // Insert into database if no upload error
        if (empty($message)) {
            $insertSql = "INSERT INTO Books (Title, Author, ISBN, CategoryID, PdfPath) VALUES (?, ?, ?, ?, ?)";
            $params = [$title, $author, $isbn, $categoryID, $pdfPath];

            $stmt = sqlsrv_query($conn, $insertSql, $params);
            if ($stmt) {
                $message = "Book added successfully.";
            } else {
                $message = "Error inserting book: " . print_r(sqlsrv_errors(), true);
            }
        }
    }

    sqlsrv_close($conn);
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
    <?php if (!empty($message)) echo "<p>$message</p>"; ?>
    <form method="POST" enctype="multipart/form-data">
        <label>Title:</label><input type="text" name="title" required><br>
        <label>Author:</label><input type="text" name="author" required><br>
        <label>ISBN:</label><input type="text" name="isbn" required><br>

        <label>Category:</label>
        <select name="category" required>
            <option value="">-- Select Category --</option>
            <?php
            // Load categories from DB
            $conn = sqlsrv_connect("WINSVR2019", [
                "Database" => "LibraryDB",
                "Uid" => "php_user",
                "PWD" => "Pa$$w0rd"
            ]);

            if ($conn) {
                $categoryQuery = "SELECT CategoryID, CategoryName FROM Category";
                $result = sqlsrv_query($conn, $categoryQuery);

                while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                    echo "<option value='{$row['CategoryID']}'>{$row['CategoryName']}</option>";
                }

                sqlsrv_close($conn);
            }
            ?>
        </select><br>

        <label>PDF File:</label>
        <input type="file" name="pdf" accept="application/pdf"><br><br>

        <button type="submit">Upload</button>
        <button type="button" onclick="window.location.href='managebooks.php'">Back</button>
    </form>
</body>
</html>
