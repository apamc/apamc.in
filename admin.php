<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli('sql302.infinityfree.com', 'if0_38105491', 'HUWVTzz1gB', 'if0_38105491_user');
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Handle form submission to add a new user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $username = trim($_POST['username']); // Trim input to avoid accidental spaces
    $password = md5(trim($_POST['password'])); // Simple hashing (use password_hash for production)
    $role = $_POST['role'];
    $redirect_url = trim($_POST['redirect_url']);

    // Prepare and execute the SQL statement
    $stmt = $conn->prepare("INSERT INTO users (username, password, role, redirect_url) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $password, $role, $redirect_url);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>New user created successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// Fetch existing users to display
$result = $conn->query("SELECT * FROM users");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container { margin-top: 50px; }
    </style>
</head>
<body>
<div class="container">
    <h1 class="text-center">Admin Panel</h1>
    <!-- Add New User Form -->
    <h2>Create New User</h2>
    <form method="POST" action="" class="p-4 border rounded bg-light">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username" id="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <select name="role" id="role" class="form-select" required>
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="redirect_url" class="form-label">Redirect URL</label>
            <input type="text" name="redirect_url" id="redirect_url" class="form-control" placeholder="e.g., user1.html" required>
        </div>
        <button type="submit" name="add_user" class="btn btn-primary">Create User</button>
    </form>
</div>
<div class="container">
<h2>Invoice</h2>
<a href="https://script.google.com/macros/s/AKfycbzC5K9gLL8s7onDO0lSvEW2-ecs44qLOYuFNT-iTc7N61IZ3FPFh8Nha0Ox_QfSD05h/exec" target="_blank" rel="noopener noreferrer">Create invoice</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
