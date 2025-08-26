<div class="table-responsive">
    <table class="table table-striped table-hover table-pets">
        <thead>
            <tr>
                <th><?php echo $L['image'] ?? 'Image'; ?></th>
                <th><?php echo $L['species'] ?? 'Species'; ?></th>
                <th><?php echo $L['breed'] ?? 'Breed'; ?></th>
                <th><?php echo $L['name'] ?? 'Name'; ?></th>
                <th><?php echo $L['sex'] ?? 'Sex'; ?></th>
                <th><?php echo $L['microchip_number'] ?? 'Microchip #'; ?></th>
                <th><?php echo $L['status'] ?? 'Status'; ?></th>
                <th><?php echo $L['presence_in_shelter'] ?? 'Presence in Shelter'; ?></th>
                <th><?php echo $L['incoming_date'] ?? 'Incoming Date'; ?></th>
                <th><?php echo $L['actions'] ?? 'Actions'; ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pets as $pet): ?>
                <tr>
                    <td>
                        <?php if (!empty($pet['image_path'])): ?>
                            <a href="<?php echo htmlspecialchars($pet['image_path']); ?>" target="_blank">
                                <img src="<?php echo htmlspecialchars($pet['image_path']); ?>" alt="<?php echo $L['image'] ?? 'Pet Image'; ?>" class="img-thumbnail" style="max-width: 60px; max-height: 60px;">
                            </a>
                        <?php else: ?>
                            <span class="text-muted"><?php echo $L['no_image_available'] ?? 'No image'; ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars(ucfirst($L[$pet['species']] ?? $pet['species'])); ?></td>
                    <td><?php echo htmlspecialchars($pet['breed']); ?></td>
                    <td><?php echo htmlspecialchars($pet['name']); ?></td>                        
                    <td><?php echo htmlspecialchars(ucfirst($L[$pet['sex']] ?? $pet['sex'])); ?></td>
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
                            <?php echo $L[$pet['status']] ?? ucfirst($pet['status']); ?>
                        </span>
                    </td>
                    <td>
                        <?php echo $pet['in_shelter'] ? '<span class="text-success">' . ($L['yes'] ?? 'Yes') . '</span>' : '<span class="text-danger">' . ($L['no'] ?? 'No') . '</span>'; ?>
                    </td>
                    <td>
                        <?php echo !empty($pet['incoming_date']) 
                            ? date('d.m.Y', strtotime($pet['incoming_date'])) 
                            : '<span class="text-muted">' . ($L['not_available'] ?? 'N/A') . '</span>'; ?>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="view-pet.php?id=<?php echo $pet['id']; ?>" class="btn btn-sm btn-info"><?php echo $L['view'] ?? 'View'; ?></a>
                            <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
                                <a href="edit-pet.php?id=<?php echo $pet['id']; ?>" class="btn btn-sm btn-warning"><?php echo $L['edit'] ?? 'Edit'; ?></a>
                            <?php endif; ?>
                            <?php if (($_SESSION['role'] === 'admin') || ($_SESSION['role'] === 'staff' && $_SESSION['user_id'] == $pet['created_by'])): ?>
                                <a href="delete-pet.php?id=<?php echo $pet['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('<?php echo $L['are_you_sure'] ?? 'Are you sure?'; ?>')">
                                    <?php echo $L['delete'] ?? 'Delete'; ?>
                                </a>
                            <?php endif; ?>
                        </div>
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
                <a class="page-link" href="?limit=<?php echo $limit; ?>&page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>"><?php echo $L['previous'] ?? 'Previous'; ?></a>
            </li>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                <a class="page-link" href="?limit=<?php echo $limit; ?>&page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>
        <?php if ($page < $total_pages): ?>
            <li class="page-item">
                <a class="page-link" href="?limit=<?php echo $limit; ?>&page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>"><?php echo $L['next'] ?? 'Next'; ?></a>
            </li>
        <?php endif; ?>
    </ul>
</nav>