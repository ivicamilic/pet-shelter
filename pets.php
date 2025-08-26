<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$lang = $_SESSION['lang'] ?? 'sr';
$L = require __DIR__ . '/lang/' . $lang . '.php';

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

// Handle AJAX requests
if (isset($_GET['ajax'])) {
    // Return only table body and pagination for AJAX
    ob_start();
    include 'pets_table.php';
    echo ob_get_clean();
    exit;
}

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <form method="get" class="me-2">
                <div class="d-flex align-items-center">
                    <label for="limit" class="form-label me-2 mb-0"><?php echo $L['show'] ?? 'Show'; ?></label>
                    <select name="limit" id="limit" class="form-select form-select-sm me-2" style="width:60px;" onchange="this.form.submit()">
                        <option value="10" <?php if($limit == 10) echo 'selected'; ?>>10</option>
                        <option value="25" <?php if($limit == 25) echo 'selected'; ?>>25</option>
                        <option value="50" <?php if($limit == 50) echo 'selected'; ?>>50</option>
                    </select>
                    <span><?php echo $L['rows'] ?? 'rows'; ?></span>
                    <input type="hidden" name="page" value="<?php echo $page; ?>">
                    <?php if (isset($_GET['search'])): ?>
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
                    <?php endif; ?>
                </div>
            </form>
        </div>
        <h2><?php echo $L['all_pets'] ?? 'All Pets'; ?></h2>
        <div class="d-flex">
            <form method="get" class="d-flex">
                <input type="text" name="search" id="searchInput" class="form-control form-control-sm me-2" placeholder="<?php echo $L['search_pets'] ?? 'Search pets...'; ?>" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" style="width: 200px;">
                <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="bi bi-search"></i> <?php echo $L['search'] ?? 'Search'; ?></button>
                <input type="hidden" name="limit" value="<?php echo $limit; ?>">
                <input type="hidden" name="page" value="1">
            </form>
            <a href="export.php?format=xls<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="btn btn-outline-success btn-sm ms-2">
                <i class="bi bi-file-earmark-excel"></i> <?php echo $L['export_xls'] ?? 'Export XLS'; ?>
            </a>
            <a href="export.php?format=pdf<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="btn btn-outline-danger btn-sm ms-2">
                <i class="bi bi-file-earmark-pdf"></i> <?php echo $L['export_pdf'] ?? 'Export PDF'; ?>
            </a>
            <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
                <a href="add-pet.php" class="btn btn-outline-primary btn-sm ms-2"><i class="bi bi-plus-circle"></i> <?php echo $L['add_new_pet'] ?? 'Add New Pet'; ?></a>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'pets_table.php'; ?>
</div>

<script>
let searchTimeout;
document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const searchValue = this.value;
    searchTimeout = setTimeout(() => {
        fetch(`pets.php?search=${encodeURIComponent(searchValue)}&limit=<?php echo $limit; ?>&ajax=1`)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTable = doc.querySelector('.table-pets tbody');
                const newPagination = doc.querySelector('.pagination');
                if (newTable) document.querySelector('.table-pets tbody').innerHTML = newTable.innerHTML;
                if (newPagination) document.querySelector('.pagination').innerHTML = newPagination.innerHTML;
            });
    }, 300);
});
</script>

<?php include 'includes/footer.php'; ?>