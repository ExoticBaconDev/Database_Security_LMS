<?php
// Handle form submission
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['back'])) {
        header("Location: dashboard.php");
        exit;
    }

    $categoryName = trim($_POST['category_name']);

    if (!empty($categoryName)) {
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

        // Check if category already exists
        $checkSql = "SELECT 1 FROM Categories WHERE CategoryName = ?";
        $checkStmt = sqlsrv_query($conn, $checkSql, array($categoryName));

        if (sqlsrv_has_rows($checkStmt)) {
            $message = "Category already exists!";
        } else {
            // Insert new category
            $insertSql = "INSERT INTO Categories (CategoryName) VALUES (?)";
            $insertStmt = sqlsrv_query($conn, $insertSql, array($categoryName));

            if ($insertStmt) {
                $message = "Category added successfully!";
            } else {
                $message = "Error adding category: " . print_r(sqlsrv_errors(), true);
            }
        }

        sqlsrv_close($conn);
    } else {
        $message = "Category name cannot be empty.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Category</title>
    <link rel="stylesheet" href="../css/addcategories.css">
</head>
<body>
    <?php if (!empty($message)) echo "<p>$message</p>"; ?>

    <form method="POST">
        <h1>Add Category</h1>
        <label>Category Name:</label>
        <input type="text" name="category_name" required><br><br>

        <button type="submit" name="add">Add</button>
        <button type="submit" name="back">Back</button>
    </form>
</body>
</html>
