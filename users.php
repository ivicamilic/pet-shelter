<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

redirectIfNotLoggedIn();
redirectIfNotAdmin();

$users = $db->fetchAll("SELECT * FROM users ORDER BY created_at DESC");

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?php echo $L['user_management'] ?? 'User Management'; ?></h2>
        <a href="register.php" class="btn btn-primary"><?php echo $L['add_new_user'] ?? 'Add New User'; ?></a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th><?php echo $L['id'] ?? 'ID'; ?></th>
                            <th><?php echo $L['username'] ?? 'Username'; ?></th>
                            <th><?php echo $L['email'] ?? 'Email'; ?></th>
                            <th><?php echo $L['full_name'] ?? 'Full Name'; ?></th>
                            <th><?php echo $L['role'] ?? 'Role'; ?></th>
                            <th><?php echo $L['created_at'] ?? 'Created At'; ?></th>
                            <th><?php echo $L['actions'] ?? 'Actions'; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td>
                                    <span class="badge 
                                        <?php 
                                        switch($user['role']) {
                                            case 'admin': echo 'bg-danger'; break;
                                            case 'staff': echo 'bg-info'; break;
                                            default: echo 'bg-secondary';
                                        }
                                        ?>">
                                        <?php echo ucfirst($L[$user['role']] ?? $user['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d.m.Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <a href="edit-user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning"><?php echo $L['edit'] ?? 'Edit'; ?></a>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="delete-user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><?php echo $L['delete'] ?? 'Delete'; ?></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>