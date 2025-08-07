<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

redirectIfNotLoggedIn();
redirectIfNotAdmin();

if (!isset($_GET['id'])) {
    header('Location: users.php');
    exit();
}

$user_id = $_GET['id'];
$user = getUserById($user_id);

if (!$user) {
    header('Location: users.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $full_name = trim($_POST['full_name']);
    $role = $_POST['role'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // Validacija
    if (empty($username) || empty($email) || empty($full_name) || empty($role)) {
        $error = "Required fields are missing.";
    } elseif (!empty($password) && $password !== $password_confirm) {
        $error = "Passwords do not match.";
    } else {
        // Provera da li korisnik već postoji (osim trenutnog)
        $existing_user = $db->fetchOne(
            "SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?",
            [$username, $email, $user_id]
        );
        
        if ($existing_user) {
            $error = "Username or email already exists.";
        } else {
            // Ažuriranje korisnika
            $update_data = [
                'username' => $username,
                'email' => $email,
                'full_name' => $full_name,
                'role' => $role,
                'id' => $user_id
            ];
            
            $sql = "UPDATE users SET username = ?, email = ?, full_name = ?, role = ?";
            $params = [$username, $email, $full_name, $role];
            
            // Ažuriranje lozinke samo ako je uneta
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql .= ", password = ?";
                $params[] = $hashed_password;
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $user_id;
            
            $db->query($sql, $params);
            
            $_SESSION['message'] = "User updated successfully!";
            header('Location: users.php');
            exit();
        }
    }
}

include 'includes/header.php';
?>

<div class="container mt-4">
    <h2>Edit User: <?php echo htmlspecialchars($user['username']); ?></h2>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="username" class="form-label">Username*</label>
                    <input type="text" class="form-control" id="username" name="username" 
                           value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email*</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name*</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" 
                           value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="role" class="form-label">Role*</label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="staff" <?php echo $user['role'] === 'staff' ? 'selected' : ''; ?>>Staff</option>
                        <option value="volunteer" <?php echo $user['role'] === 'volunteer' ? 'selected' : ''; ?>>Volunteer</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="password" name="password">
                    <small class="text-muted">Leave blank to keep current password</small>
                </div>
                <div class="mb-3">
                    <label for="password_confirm" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="password_confirm" name="password_confirm">
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Update User</button>
            <a href="users.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>