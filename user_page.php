<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Connect to the database
$conn = new mysqli('sql302.infinityfree.com', 'if0_38105491', 'HUWVTzz1gB', 'if0_38105491_user');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the logged-in user's details
$username = $_SESSION['username'];
$result = $conn->query("SELECT * FROM users WHERE username = '$username'");

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "<h1>Welcome to Your Page, {$user['username']}!</h1>";
    echo "<p>Your role is: {$user['role']}</p>";
} else {
    echo "<p>User not found.</p>";
}
echo "<p><a href='dashboard.php'>Back to Dashboard</a></p>";
?>
