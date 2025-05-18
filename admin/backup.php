<?php
// Only allow admin to access backup
session_start();
if (!isset($_SESSION['user_name']) || $_SESSION['user_role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

$backupFile = "librarydb_backup_" . date("Y-m-d_H-i-s") . ".bak";
$cmd = "sqlcmd -S localhost -U php_user -P Pa$$w0rd -Q "BACKUP DATABASE [LibraryDB] TO DISK='C:\\xampp\\htdocs\\Database_Security_LMS-main\\backups\\$backupFile'"";
system($cmd);
echo "<p>Backup created: $backupFile</p>";
?>
