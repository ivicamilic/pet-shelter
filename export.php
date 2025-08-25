<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$lang = $_SESSION['lang'] ?? 'sr';
$L = require __DIR__ . '/lang/' . $lang . '.php';

redirectIfNotLoggedIn();

$format = $_GET['format'] ?? 'xls';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get pets data
$params = [];
$sql = "SELECT * FROM pets";
$where = "";

if ($search !== '') {
    $where = " WHERE name LIKE ? OR breed LIKE ? OR microchip_number LIKE ? ";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= $where . " ORDER BY id DESC";
$pets = $db->fetchAll($sql, $params);

if ($format === 'xls') {
    // Export to Excel
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="pets_export.xls"');
    
    echo "<table border='1'>";
    echo "<tr>";
    echo "<th>" . ($L['name'] ?? 'Name') . "</th>";
    echo "<th>" . ($L['species'] ?? 'Species') . "</th>";
    echo "<th>" . ($L['breed'] ?? 'Breed') . "</th>";
    echo "<th>" . ($L['sex'] ?? 'Sex') . "</th>";
    echo "<th>" . ($L['microchip_number'] ?? 'Microchip #') . "</th>";
    echo "<th>" . ($L['status'] ?? 'Status') . "</th>";
    echo "<th>" . ($L['presence_in_shelter'] ?? 'Presence in Shelter') . "</th>";
    echo "<th>" . ($L['incoming_date'] ?? 'Incoming Date') . "</th>";
    echo "</tr>";
    
    foreach ($pets as $pet) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($pet['name']) . "</td>";
        echo "<td>" . htmlspecialchars($L[$pet['species']] ?? $pet['species']) . "</td>";
        echo "<td>" . htmlspecialchars($pet['breed']) . "</td>";
        echo "<td>" . htmlspecialchars($L[$pet['sex']] ?? $pet['sex']) . "</td>";
        echo "<td>" . htmlspecialchars($pet['microchip_number']) . "</td>";
        echo "<td>" . htmlspecialchars($L[$pet['status']] ?? $pet['status']) . "</td>";
        echo "<td>" . ($pet['in_shelter'] ? ($L['yes'] ?? 'Yes') : ($L['no'] ?? 'No')) . "</td>";
        echo "<td>" . (!empty($pet['incoming_date']) ? date('d.m.Y', strtotime($pet['incoming_date'])) : '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} elseif ($format === 'pdf') {
    // HTML page for printing to PDF
    header('Content-Type: text/html; charset=UTF-8');
    
    echo '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>' . ($L['pets_report'] ?? 'Pets Report') . '</title>
        <style>
            @media print { .no-print { display: none; } }
            body { font-family: Arial, sans-serif; margin: 20px; }
            table { width: 100%; border-collapse: collapse; font-size: 12px; }
            th, td { border: 1px solid #000; padding: 5px; text-align: left; vertical-align: middle; }
            th { background-color: #f0f0f0; font-weight: bold; }
            img { max-width: 50px; max-height: 50px; object-fit: cover; }
            h2 { text-align: center; margin-bottom: 20px; }
            .print-btn { margin: 20px 0; text-align: center; }
        </style>
        <script>
            window.onload = function() {
                window.print();
            }
        </script>
    </head>
    <body>
        <div class="no-print print-btn">
            <button onclick="window.print()">Print to PDF</button>
            <button onclick="window.close()">Close</button>
        </div>
        <h2>' . ($L['pets_report'] ?? 'Pets Report') . '</h2>
        <table>
            <tr>
                <th>' . ($L['image'] ?? 'Image') . '</th>
                <th>' . ($L['name'] ?? 'Name') . '</th>
                <th>' . ($L['species'] ?? 'Species') . '</th>
                <th>' . ($L['breed'] ?? 'Breed') . '</th>
                <th>' . ($L['sex'] ?? 'Sex') . '</th>
                <th>' . ($L['microchip_number'] ?? 'Microchip #') . '</th>
                <th>' . ($L['status'] ?? 'Status') . '</th>
                <th>' . ($L['presence_in_shelter'] ?? 'Presence') . '</th>
                <th>' . ($L['incoming_date'] ?? 'Date') . '</th>
            </tr>';
    
    foreach ($pets as $pet) {
        $imageCell = !empty($pet['image_path']) ? '<img src="' . htmlspecialchars($pet['image_path']) . '" style="width:50px;height:50px;object-fit:cover;">' : 'No image';
        echo '<tr>
                <td>' . $imageCell . '</td>
                <td>' . htmlspecialchars($pet['name']) . '</td>
                <td>' . htmlspecialchars($L[$pet['species']] ?? $pet['species']) . '</td>
                <td>' . htmlspecialchars($pet['breed']) . '</td>
                <td>' . htmlspecialchars($L[$pet['sex']] ?? $pet['sex']) . '</td>
                <td>' . htmlspecialchars($pet['microchip_number']) . '</td>
                <td>' . htmlspecialchars($L[$pet['status']] ?? $pet['status']) . '</td>
                <td>' . ($pet['in_shelter'] ? ($L['yes'] ?? 'Yes') : ($L['no'] ?? 'No')) . '</td>
                <td>' . (!empty($pet['incoming_date']) ? date('d.m.Y', strtotime($pet['incoming_date'])) : '') . '</td>
              </tr>';
    }
    
    echo '</table>
    </body>
    </html>';
}
?>