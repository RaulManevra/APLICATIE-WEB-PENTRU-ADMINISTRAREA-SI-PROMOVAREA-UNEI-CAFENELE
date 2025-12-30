<?php
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit('Direct access denied.');
}

if (!isset($conn)) {
    require_once __DIR__ . '/../../config/db.php';
}

$sql = "SELECT * FROM tables ORDER BY ID ASC";
$result = $conn->query($sql);

// Background Logic
$bgPath = null;
$bgDirRel = 'assets/uploads/floor_plan/';
$bgDirAbs = __DIR__ . '/../../' . $bgDirRel; // adjust path relative to views/pages
$extensions = ['png', 'jpg', 'jpeg', 'gif'];
foreach ($extensions as $ext) {
    if (file_exists($bgDirAbs . 'layout.' . $ext)) {
        $bgPath = $bgDirRel . 'layout.' . $ext . '?t=' . time();
        
        // Get image dimensions to fix aspect ratio and prevent stretching/misalignment
        list($width, $height) = getimagesize($bgDirAbs . 'layout.' . $ext);
        $bgStyle = "background-image: url('$bgPath'); background-size: cover; aspect-ratio: $width / $height; height: auto;";
        break;
    }
}
if(!$bgPath) $bgStyle = "";
?>
<link rel="stylesheet" href="assets/css/tables.css?v=<?= time() ?>">

<div class="tables-page">
    <div class="tables-intro">
        <h2>Live Table Availability</h2>
        <p>Verificați planul cafenelei noastre pentru a vedea ce mese sunt libere în timp real.</p>
    </div>

    <!-- Map Container -->
    <div class="map-container" style="<?= $bgStyle ?>">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <?php 
                    $x = $row['x_pos'] ?? 10;
                    $y = $row['y_pos'] ?? 10;
                    $w = $row['width'] ?? 60;
                    $h = $row['height'] ?? 60;
                    $shape = $row['shape'] ?? 'circle';
                    
                    $borderRadius = '50%';
                    if($shape === 'square' || $shape === 'rectangle') $borderRadius = '8px';
                    
                    $status = strtolower($row['Status'] ?? 'inactiva');
                    $statusClass = 'status-' . str_replace(' ', '-', $status);
                ?>
                <div class="map-table <?= $statusClass ?>" 
                     style="left: <?= $x ?>%; top: <?= $y ?>%; width: <?= $w ?>px; height: <?= $h ?>px; border-radius: <?= $borderRadius ?>;" 
                     title="Table <?= $row['ID'] ?> - <?= $row['Status'] ?>">
                    <?= $row['ID'] ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="padding: 20px;">Layout not available.</p>
        <?php endif; ?>
    </div>

    <div class="legend">
        <div class="legend-item">
            <div class="legend-color status-libera"></div>
            <span>Available</span>
        </div>
        <div class="legend-item">
            <div class="legend-color status-ocupata"></div>
            <span>Occupied</span>
        </div>
        <div class="legend-item">
            <div class="legend-color status-rezervata"></div>
            <span>Reserved</span>
        </div>
    </div>
</div>