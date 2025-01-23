<?php
session_start();
$conn = new mysqli('sql302.infinityfree.com', 'if0_38105491', 'HUWVTzz1gB', 'if0_38105491_user');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = md5($_POST['password']); // Use MD5 for simplicity, prefer password_hash in production

    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['redirect_url'] = $row['redirect_url'];

        // Redirect to the user's specific URL if defined, otherwise to dashboard
        $redirect_url = !empty($row['redirect_url']) ? $row['redirect_url'] : 'dashboard.php';
        header("Location: $redirect_url");
    } else {
        echo "Invalid credentials!";
    }
}
?>
