<?php
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit('Direct access denied.');
}

if (!isset($conn)) {
    require_once __DIR__ . '/../../config/db.php';
}
require_once __DIR__ . '/../../core/SessionManager.php';
require_once __DIR__ . '/../../core/csrf.php';

// Fetch Tables
$sql = "SELECT * FROM tables ORDER BY ID ASC";
$result = $conn->query($sql);

// Fetch Active Reservations for Dynamic Status
// Window: [Now - 1h, Now + 20m]
$resSql = "
    SELECT table_id, reservation_time 
    FROM reservations 
    WHERE reservation_time BETWEEN DATE_SUB(NOW(), INTERVAL 1 HOUR) AND DATE_ADD(NOW(), INTERVAL 20 MINUTE)
";
$resResult = $conn->query($resSql);
$activeReservations = [];
if ($resResult) {
    while ($r = $resResult->fetch_assoc()) {
        $activeReservations[$r['table_id']] = $r['reservation_time'];
    }
}

// Background Logic
$bgPath = null;
$bgDirRel = 'assets/uploads/floor_plan/';
$bgDirAbs = __DIR__ . '/../../' . $bgDirRel;
$extensions = ['png', 'jpg', 'jpeg', 'gif'];
foreach ($extensions as $ext) {
    if (file_exists($bgDirAbs . 'layout.' . $ext)) {
        $bgPath = $bgDirRel . 'layout.' . $ext . '?t=' . time();
        list($width, $height) = getimagesize($bgDirAbs . 'layout.' . $ext);
        $bgStyle = "background-image: url('$bgPath'); background-size: cover; aspect-ratio: $width / $height; height: auto;";
        break;
    }
}
if (!$bgPath) $bgStyle = "";

$isLoggedIn = SessionManager::isLoggedIn();
?>
<!-- Inject CSRF for JS -->
<input type="hidden" id="csrf-token-global" value="<?= csrf_token() ?>">
<link rel="stylesheet" href="assets/css/tables.css?v=<?= time() ?>">
<style>
    /* Modal Styles (Embedded for simplicity or move to css) */
    .res-modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(5px);
    }

    .res-modal-content {
        background-color: #ffffffab;
        margin: 10% auto;
        padding: 30px;
        border-radius: 12px;
        width: 90%;
        max-width: 400px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        animation: fadeIn 0.3s;
        text-align: center;
    }

    .res-modal h3 {
        margin-top: 0;
        color: #333;
    }

    .res-close {
        float: right;
        font-size: 24px;
        font-weight: bold;
        cursor: pointer;
        color: #a83131d1;
    }

    .res-close:hover {
        color: #ff3c3cff;
    }

    .res-form input {
        width: 100%;
        padding: 12px;
        margin-bottom: 10px;
        border: 1px solid #ddd;
        border-radius: 10px;
        font-size: 16px;
        cursor: pointer;
        outline: none;
    }

    .res-form label {
        display: block;
        font-weight: 600;
        margin-top: 12px;
        margin-bottom: 4px;
        color: #333;
        text-align: left;
    }

    .res-btn {
        background: #27ae60;
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 20px;
        cursor: pointer;
        font-size: 16px;
        width: 100%;
        transition: background 0.2s;
    }

    .res-btn:hover {
        background: #35e67fff;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .map-table.clickable:hover {
        cursor: pointer;
        transform: scale(1.05);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        border: 1px solid black !important;
    }
</style>

<div class="tables-page">
    <div class="tables-intro">
        <h2>Live Table Availability</h2>
        <p>Tap an available table to reserve (Verified Users Only).</p>
    </div>

    <!-- Map Container -->
    <div class="map-layout">
        <div class="map-container" style="<?= $bgStyle ?>">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                    $id = $row['ID'];
                    $x = $row['x_pos'] ?? 10;
                    $y = $row['y_pos'] ?? 10;
                    $w = $row['width'] ?? 60;
                    $h = $row['height'] ?? 60;
                    $shape = $row['shape'] ?? 'circle';

                    $borderRadius = '50%';
                    if ($shape === 'square' || $shape === 'rectangle') $borderRadius = '8px';

                    $status = strtolower(trim($row['Status'] ?? 'inactiva'));

                    // Dynamic Override
                    if ($status !== 'inactiva') {
                        if (isset($activeReservations[$id])) {
                            $status = 'rezervata';
                        } elseif ($status !== 'ocupata' && $status !== 'libera') {
                            // Fallback if DB has weird value, default to ocupata or keep as is?
                            // Keep as is.
                        }
                    }

                    $statusClass = 'status-' . str_replace(' ', '-', $status);

                    // Clickable only if Libera
                    $isClickable = ($status === 'libera');
                    $clickAttr = $isClickable ? "onclick='openResModal($id)'" : "";
                    $cursorClass = $isClickable ? "clickable" : "";
                    ?>
                    <div class="map-table <?= $statusClass ?> <?= $cursorClass ?>"
                        style="left: <?= $x ?>%; top: <?= $y ?>%; width: <?= $w ?>%; height: <?= $h ?>%; border-radius: <?= $borderRadius ?>;"
                        title="Table <?= $id ?> - <?= ucfirst($status) ?>"
                        <?= $clickAttr ?>>
                        <?= $id ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="padding: 20px;">Layout not available.</p>
            <?php endif; ?>
        </div>

        <div class="legend">
            <div class="legend-item">
                <div class="legend-color status-libera"></div>
                <span class="badge available">Available (Click to Reserve)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color status-ocupata"></div>
                <span class="badge occupied">Occupied</span>
            </div>
            <div class="legend-item">
                <div class="legend-color status-rezervata"></div>
                <span class="badge reserved">Reserved/Busy</span>
            </div>
        </div>
    </div>
</div>

<?php
$currentUser = SessionManager::getCurrentUserData();
$displayUsername = $currentUser ? htmlspecialchars($currentUser['username']) : 'Guest';
?>
<!-- Reservation Modal -->
<div id="reservation-modal" class="res-modal">
    <div class="res-modal-content">
        <span class="res-close" onclick="closeResModal()">&times;</span>
        <h3>Reserve Table <span id="res-table-id"></span></h3>
        <form id="reservation-form" class="res-form">
            <input type="hidden" id="res_table_id_input" name="table_id">
            <input type="hidden" name="action" value="create">

            <!-- 1. Name Input -->
            <label for="res-name">Name</label>
            <input type="text" id="res-name" name="name" 
                   value="<?= $displayUsername !== 'Guest' ? $displayUsername : '' ?>" 
                   placeholder="Your Name" required maxlength="50">

            <!-- 2. Date Input -->
            <label for="res-date">Date</label>
            <input type="date" id="res-date" name="date" required min="<?= date('Y-m-d') ?>">

            <!-- 3. Time Input -->
            <label for="res-time">Time</label>
            <input type="time" id="res-time" name="time" required>

            <button type="submit" class="res-btn">Confirm Reservation</button>
        </form>
    </div>
</div>

<script>
    var isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;

    function openResModal(id) {
        // Dynamic Check
        fetch('controllers/check_session.php')
            .then(r => r.json())
            .then(data => {
                if (!data.loggedIn) {
                    if (confirm("Reservations can only be made by verified users. \nDo you want to login now?")) {
                        window.location.href = "index.php?page=login";
                    }
                    return;
                }
                // Logged in, proceed
                document.getElementById('res-table-id').textContent = id;
                document.getElementById('res_table_id_input').value = id;
                document.getElementById('reservation-modal').style.display = 'block';
            })
            .catch(err => {
                console.error("Auth check failed", err);
                alert("Could not verify session. Please try again.");
            });
    }

    function closeResModal() {
        document.getElementById('reservation-modal').style.display = 'none';
    }

    // Submit Logic
    document.getElementById('reservation-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = this.querySelector('button');
        const originalText = btn.textContent;
        btn.textContent = "Processing...";
        btn.disabled = true;

        const formData = new FormData(this);
        // Use Global csrf if available
        const csrf = document.getElementById('csrf-token-global');
        if (csrf) formData.append('csrf_token', csrf.value);

        fetch('controllers/reservation_handler.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert("Reservation confirmed!");
                    closeResModal();
                    if (window.loadPage) window.loadPage('tables');
                    else location.reload();
                } else {
                    if (data.conflict) {
                        // Advanced Conflict Handling
                        const modalBody = document.querySelector('.res-modal-content');

                        let altHtml = '';
                        if (data.alternative_tables && data.alternative_tables.length > 0) {
                            altHtml = `<div style="margin-top:10px;">
                                      <strong>Available Tables at ${document.querySelector('input[name="date"]').value.replace('T', ' ')}:</strong><br>
                                      ${data.alternative_tables.map(tid => 
                                          `<button type="button" class="res-btn-alt" onclick="switchTable(${tid})">Table ${tid}</button>`
                                      ).join(' ')}
                                    </div>`;
                        } else {
                            altHtml = `<p>No other tables available at this time.</p>`;
                        }

                        const nextDate = new Date(data.next_available);
                        // Format for input: YYYY-MM-DDTHH:mm
                        // Adjust to local timezone string 
                        // Simple hack for ISO string locally
                        const tzOffset = nextDate.getTimezoneOffset() * 60000; // offset in milliseconds
                        const localISOTime = (new Date(nextDate - tzOffset)).toISOString().slice(0, 16);

                        const currentTableId = document.getElementById('res_table_id_input').value;

                        const conflictHtml = `
                        <div id="conflict-resolution" style="background:#fff3cd; padding:10px; border:1px solid #ffeeba; margin-top:10px; border-radius:5px; color:#856404;">
                            <p><strong>Refused:</strong> ${data.message}</p>
                            <div style="margin-top:10px;">
                                <strong>Option A: Same Table, Next Slot</strong><br>
                                <button type="button" class="res-btn-alt" onclick="setNewTime('${localISOTime}')">
                                    Reserve for ${data.next_available.substring(11, 16)}
                                </button>
                            </div>
                            ${altHtml}
                            <button type="button" onclick="resetForm()" style="margin-top:15px; background:none; border:none; color:blue; text-decoration:underline; cursor:pointer;">Cancel / Pick Manually</button>
                        </div>
                     `;

                        // Inject into modal
                        // Hide form temporarily? Or just append?
                        // Let's replace the form or append below it.
                        const existing = document.getElementById('conflict-resolution');
                        if (existing) existing.remove();

                        const form = document.getElementById('reservation-form');
                        form.style.display = 'none'; // Hide form
                    } else {
                        // Generic Error (Constraint violation etc)
                        console.warn("Reservation Error:", data);

                        const modalContent = document.querySelector('.res-modal-content');

                        // Remove old errors/conflicts
                        const oldConflict = document.getElementById('conflict-resolution');
                        if (oldConflict) oldConflict.remove();

                        let errorBox = document.getElementById('res-error-box');
                        if (errorBox) errorBox.remove();

                        // Create new error box
                        errorBox = document.createElement('div');
                        errorBox.id = 'res-error-box';
                        errorBox.style.cssText = 'background:#f8d7da; color:#721c24; padding:15px; border-radius:8px; margin-bottom:15px; border:1px solid #f5c6cb; font-weight:bold; animation: fadeIn 0.3s;';
                        errorBox.innerHTML = `⚠️ Reservation Failed:<br><span style="font-weight:normal;">${data.message || data.error || "Unknown Error"}</span>`;

                        // Insert at the VERY TOP of content
                        modalContent.insertBefore(errorBox, modalContent.firstChild); // Before close button? No, close button is float.
                        // Let's insert after H3 title? Or just prepend.
                        // Prepend is safer.

                        // Make sure form is visible (if hidden by state)
                        const form = document.getElementById('reservation-form');
                        form.style.display = 'block';

                        // Scroll to top
                        modalContent.scrollTop = 0;
                    }
                }
            })
            .catch(err => {
                console.error("Fetch Error:", err);
                alert("Connection Failed: " + err);
            })
            .finally(() => {
                btn.textContent = originalText;
                btn.disabled = false;
            });
    });

    // Helper to resolve conflict
    window.switchTable = function(newId) {
        document.getElementById('conflict-resolution').remove();
        document.getElementById('reservation-form').style.display = 'block';
        openResModal(newId); // Re-open for new ID (updates hidden input and title)
        // Keep the entered time? 
        // openResModal resets?
        // Let's manually update
        // openResModal(newId) -> sets values.
        // But date input might be cleared if openResModal resets?
        // openResModal implementation doesn't reset date.
        // So just switching ID is enough.
    };

    window.setNewTime = function(isoStr) {
        document.querySelector('input[name="date"]').value = isoStr;
        document.getElementById('conflict-resolution').remove();
        document.getElementById('reservation-form').style.display = 'block';
        // Auto submit? Or let user confirm? 
        // Let user confirm.
    };

    window.resetForm = function() {
        const c = document.getElementById('conflict-resolution');
        if (c) c.remove();
        document.getElementById('reservation-form').style.display = 'block';
    };

    // Outside click
    window.onclick = function(event) {
        const modal = document.getElementById('reservation-modal');
        if (event.target == modal) {
            closeResModal();
        }
    }
</script>