<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$lang = $_SESSION['lang'] ?? 'en';
$L = require __DIR__ . '/lang/' . $lang . '.php';

redirectIfNotLoggedIn();

$stats = getPetStats();
$recent_activity = getRecentActivity();

include 'includes/header.php';
?>

<div class="container mt-4">
    <h2><?php echo $L['dashboard'] ?? 'Dashboard'; ?></h2>
    <p><?php echo $L['welcome'] ?? 'Welcome'; ?>, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</p>
    
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><?php echo $L['recent_activity'] ?? 'Recent Activity'; ?></h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><?php echo $L['breed'] ?? 'Breed'; ?></th>                                
                                <th><?php echo $L['title'] ?? 'Title'; ?></th>
                                <th><?php echo $L['microchip_number'] ?? 'Microchip Number'; ?> #</th>
                                <th><?php echo $L['created_by'] ?? 'Created By'; ?></th>
                                <th><?php echo $L['created_at'] ?? 'Created At'; ?></th>
                                
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_activity as $activity): ?>
                                <tr>
                                    <td><?php echo ucfirst($activity['breed']); ?></td>
                                    <td><?php echo htmlspecialchars($activity['title']); ?></td>
                                    <td><?php echo htmlspecialchars($activity['microchip_number']); ?></td>
                                    <td><?php echo htmlspecialchars($activity['created_by']); ?></td>
                                    <td><?php echo date('d.m.Y H:i', strtotime($activity['created_at'])); ?></td>
                                    
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5><?php echo $L['pet_status'] ?? 'Pet Status'; ?></h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <?php foreach ($stats as $stat): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo $L[$stat['status']] ??  ucfirst($stat['status']); ?>
                                <span class="badge bg-primary rounded-pill"><?php echo $stat['count']; ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5><?php echo $L['actions'] ?? 'Actions'; ?></h5>
                </div>
                <div class="card-body">
                    <a href="add-pet.php" class="btn btn-primary mb-2"><?php echo $L['add_pet'] ?? 'Add Pet'; ?></a>
                    <a href="pets.php" class="btn btn-secondary mb-2"><?php echo $L['view_pets'] ?? 'View Pets'; ?></a>
                    <?php if (isAdmin()): ?>
                        <a href="register.php" class="btn btn-outline-primary"><?php echo $L['register_user'] ?? 'Register User'; ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>