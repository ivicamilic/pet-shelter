<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header('Location: pets.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        $error = $L['error_fields_required'] ?? 'Both username and password are required.';
    } elseif (loginUser($username, $password)) {
        header('Location: pets.php');
        exit();
    } else {
        $error = $L['error_invalid_credentials'] ?? 'Invalid username or password.';
    }
}

include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4><?php echo $L['login'] ?? 'Login'; ?></h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-success"><?php echo $_SESSION['message']; ?></div>
                        <?php unset($_SESSION['message']); ?>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label"><?php echo $L['username'] ?? 'Username'; ?></label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label"><?php echo $L['password'] ?? 'Password'; ?></label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary"><?php echo $L['login'] ?? 'Login'; ?></button>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <?php echo $L['dont_have_account'] ?? 'Don\'t have an account?'; ?> <a href="register.php"><?php echo $L['register'] ?? 'Register'; ?></a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>