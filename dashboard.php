<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

echo "Welcome, " . $_SESSION['username'] . " (" . $_SESSION['role'] . ")";

// Link to the user's personal page
echo "<p><a href='user_page.php'>Go to Your Page</a></p>";

if ($_SESSION['role'] == 'admin') {
    echo "<p><a href='admin.php'>Go to Admin Panel</a></p>";
}

echo "<p><a href='logout.php'>Logout</a></p>";
?>
