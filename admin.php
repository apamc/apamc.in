<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli('sql305.infinityfree.com', 'if0_38162511', 'rY2e7GSDAI3XNm', 'if0_38162511_users');
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Handle user creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_user'])) {
    $username = trim($_POST['username']);
    $password = md5(trim($_POST['password']));
    $role = $_POST['role'];
    $name = trim($_POST['name']);
    $account_no = trim($_POST['account_no']);
    $units = trim($_POST['units']);
    $current_nav = trim($_POST['current_nav']);
    $invest_value = trim($_POST['invest_value']);
    $avg_nav = trim($_POST['avg_nav']);
    $current_value = $units * $current_nav;
    $returns = $current_value - $invest_value;

    // Add user to `users` table
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password, $role);
    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;
        $stmt->close();

        // Add data to `user_data` table
        $stmt = $conn->prepare("INSERT INTO user_data (user_id, name, account_no, units, current_nav, invest_value, avg_nav, current_value, returns) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issdddidd", $user_id, $name, $account_no, $units, $current_nav, $invest_value, $avg_nav, $current_value, $returns);
        $stmt->execute();
        $stmt->close();
        echo "<div class='alert alert-success'>User created successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}

// Handle portfolio data update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_portfolio'])) {
    $user_id = $_POST['user_id'];
    $units = trim($_POST['units']);
    $current_nav = trim($_POST['current_nav']);
    $invest_value = trim($_POST['invest_value']);
    $avg_nav = trim($_POST['avg_nav']);
    $current_value = $units * $current_nav;
    $returns = $current_value - $invest_value;

    $stmt = $conn->prepare("UPDATE user_data 
                            SET units = ?, current_nav = ?, invest_value = ?, avg_nav = ?, current_value = ?, returns = ? 
                            WHERE user_id = ?");
    $stmt->bind_param("ddddddi", $units, $current_nav, $invest_value, $avg_nav, $current_value, $returns, $user_id);

    if ($stmt->execute()) {
        // No success message shown for individual user portfolio updates
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
    $stmt->close();
}

// Handle common Current NAV update for all users
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_current_nav'])) {
    $common_nav = trim($_POST['common_nav']);

    // Update all user portfolios with the new common NAV
    $stmt = $conn->prepare("UPDATE user_data SET current_nav = ?, current_value = units * ? WHERE current_nav != ?");
    $stmt->bind_param("ddd", $common_nav, $common_nav, $common_nav);
    $stmt->execute();
    $stmt->close();
    echo "<div class='alert alert-success'>All user portfolios updated with new Current NAV!</div>";
}

// Fetch all users
$users = $conn->query("SELECT u.id, u.username, u.role, d.name, d.account_no, d.units, d.current_nav, d.invest_value, d.avg_nav, d.current_value, d.returns 
                        FROM users u LEFT JOIN user_data d ON u.id = d.user_id");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2 class="mb-4">Admin Panel</h2>

        <!-- Current NAV Update Form -->
        <h4>Set Common Current NAV for All Users</h4>
        <form method="POST" action="" class="border p-4 mb-5 bg-light">
            <div class="mb-3">
                <label for="common_nav" class="form-label">Common Current NAV</label>
                <input type="number" name="common_nav" id="common_nav" class="form-control" step="0.01" required>
            </div>
            <button type="submit" name="update_current_nav" class="btn btn-warning">Update All User Portfolios</button>
        </form>

        <!-- Create User Form -->
        <h4>Create New User</h4>
        <form method="POST" action="" class="border p-4 mb-5 bg-light">
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
                <label for="name" class="form-label">Name</label>
                <input type="text" name="name" id="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="account_no" class="form-label">Folio number</label>
                <input type="text" name="account_no" id="account_no" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="units" class="form-label">Units</label>
                <input type="number" name="units" id="units" class="form-control" step="0.001" required>
            </div>
            <div class="mb-3">
                <label for="current_nav" class="form-label">Current NAV</label>
                <input type="number" name="current_nav" id="current_nav" class="form-control" step="0.01" required>
            </div>
            <div class="mb-3">
                <label for="invest_value" class="form-label">Invest Value</label>
                <input type="number" name="invest_value" id="invest_value" class="form-control" step="0.01" required>
            </div>
            <div class="mb-3">
                <label for="avg_nav" class="form-label">Average NAV</label>
                <input type="number" name="avg_nav" id="avg_nav" class="form-control" step="0.01" required>
            </div>
            <button type="submit" name="create_user" class="btn btn-primary">Create User</button>
            <a href="logout.php" class="btn btn-danger">Logout</a>
            <a href="a-signup.php" class="btn btn-info">SignUp</a>
            <a href="transaction.php" class="btn btn-secondary">Transactions</a>
            <a href="a-invest.php" class="btn btn-success">Investment</a>
            <a href="a-redeem.php" class="btn btn-success">Reedem</a>
            <a href="https://script.google.com/macros/s/AKfycbx0xR5_bzFOY-Tw96U_2VVtxVWt0q3EGHvz8Li75oMH5z4k0NFwOfHivNMEIDEVTxjFeg/exec"
                target="blank" class="btn btn-warning">Invoice</a>
        </form>

        <!-- User List and Portfolio Update -->
        <h4>All Users</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Name</th>
                    <th>Folio number</th>
                    <th>Units</th>
                    <th>Current NAV</th>
                    <th>Invest Value</th>
                    <th>Average NAV</th>
                    <th>Current Value</th>
                    <th>Returns</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['role']) ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['account_no']) ?></td>
                        <td><?= htmlspecialchars($row['units']) ?></td>
                        <td><?= htmlspecialchars($row['current_nav']) ?></td>
                        <td><?= htmlspecialchars($row['invest_value']) ?></td>
                        <td><?= htmlspecialchars($row['avg_nav']) ?></td>
                        <td><?= htmlspecialchars($row['current_value']) ?></td>
                        <td><?= htmlspecialchars($row['returns']) ?></td>
                        <td>
                            <!-- Update Portfolio Form -->
                            <form method="POST" action="" class="d-inline">
                                <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                <input type="number" name="units" class="form-control mb-2" step="0.001"
                                    value="<?= htmlspecialchars($row['units']) ?>">
                                <input type="number" name="current_nav" class="form-control mb-2" step="0.01"
                                    value="<?= htmlspecialchars($row['current_nav']) ?>">
                                <input type="number" name="invest_value" class="form-control mb-2" step="0.01"
                                    value="<?= htmlspecialchars($row['invest_value']) ?>">
                                <input type="number" name="avg_nav" class="form-control mb-2" step="0.01"
                                    value="<?= htmlspecialchars($row['avg_nav']) ?>">
                                <button type="submit" name="update_portfolio" class="btn btn-success btn-sm">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>