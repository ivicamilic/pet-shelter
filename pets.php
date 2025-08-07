<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

redirectIfNotLoggedIn();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

// Get total pets count for pagination
$total_pets_row = $db->fetchOne("SELECT COUNT(*) AS cnt FROM pets");
$total_pets = $total_pets_row['cnt'];
$total_pages = ceil($total_pets / $limit);

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$params = [];
$sql = "SELECT * FROM pets";
$where = "";

if ($search !== '') {
    $where = " WHERE name LIKE ? OR breed LIKE ? OR microchip_number LIKE ? ";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= $where . " ORDER BY id DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

$pets = $db->fetchAll($sql, $params);

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <form method="get" class="me-2">
                <div class="d-flex align-items-center">
                    <label for="limit" class="form-label me-2 mb-0">Show</label>
                    <select name="limit" id="limit" class="form-select me-2" style="width: 80px;" onchange="this.form.submit()">
                        <option value="10" <?php if($limit == 10) echo 'selected'; ?>>10</option>
                        <option value="25" <?php if($limit == 25) echo 'selected'; ?>>25</option>
                        <option value="50" <?php if($limit == 50) echo 'selected'; ?>>50</option>
                    </select>
                    <span>rows</span>
                    <input type="hidden" name="page" value="<?php echo $page; ?>">
                    <?php if (isset($_GET['search'])): ?>
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
                    <?php endif; ?>
                </div>
            </form>
        </div>
        <h2>All Pets</h2>
        <div class="d-flex">
            <form method="get" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Search pets..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit" class="btn btn-outline-primary">Search</button>
                <input type="hidden" name="limit" value="<?php echo $limit; ?>">
                <input type="hidden" name="page" value="1">
            </form>
            <?php if ($_SESSION['role'] !== 'Volunteer'): ?>
                <a href="add-pet.php" class="btn btn-primary ms-2">Add New Pet</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Image</th>
                    
                    <th>Species</th>
                    <th>Breed</th>
                    <th>Name</th>
                    <th>Sex</th>
                    <th>Microchip #</th>
                    <th>Status</th>
                    <th>Presence in Shelter</th>
                    <th>Incoming Date</th> <!-- NEW COLUMN -->
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pets as $pet): ?>
                    <tr>
                        <td>
                            <?php if (!empty($pet['image_path'])): ?>
                                <a href="<?php echo htmlspecialchars($pet['image_path']); ?>" target="_blank">
                                    <img src="<?php echo htmlspecialchars($pet['image_path']); ?>" alt="Pet Image" class="img-thumbnail" style="max-width: 60px; max-height: 60px;">
                                </a>
                            <?php else: ?>
                                <span class="text-muted">No image</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo ucfirst(htmlspecialchars($pet['species'])); ?></td>
                        <td><?php echo htmlspecialchars($pet['breed']); ?></td>
                        <td><?php echo htmlspecialchars($pet['name']); ?></td>                        
                        <td><?php echo ucfirst(htmlspecialchars($pet['sex'])); ?></td>
                        <td><?php echo htmlspecialchars($pet['microchip_number']); ?></td>
                        <td>
                            <span class="badge 
                                <?php 
                                switch($pet['status']) {
                                    case 'available': echo 'bg-success'; break;
                                    case 'adopted': echo 'bg-secondary'; break;
                                    case 'fostered': echo 'bg-info'; break;
                                    case 'medical': echo 'bg-warning'; break;
                                    default: echo 'bg-light text-dark';
                                }
                                ?>">
                                <?php echo ucfirst($pet['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php echo $pet['in_shelter'] ? '<span class="text-success">Yes</span>' : '<span class="text-danger">No</span>'; ?>
                        </td>
                        <td>
                            <?php echo !empty($pet['incoming_date']) 
                                ? date('d.m.Y', strtotime($pet['incoming_date'])) 
                                : '<span class="text-muted">N/A</span>'; ?>
                        </td>
                        <td>
                            <a href="view-pet.php?id=<?php echo $pet['id']; ?>" class="btn btn-sm btn-info">View</a>
                            <?php if ($_SESSION['role'] !== 'Volunteer' && ($_SESSION['user_id'] == $pet['created_by'] || isAdmin())): ?>
                                <a href="edit-pet.php?id=<?php echo $pet['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <nav aria-label="Pets pagination">
        <ul class="pagination justify-content-center mt-4">
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?limit=<?php echo $limit; ?>&page=<?php echo $page - 1; ?>">Previous</a>
                </li>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                    <a class="page-link" href="?limit=<?php echo $limit; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?limit=<?php echo $limit; ?>&page=<?php echo $page + 1; ?>">Next</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>

<?php include 'includes/footer.php'; ?>