<?php 
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit('Direct access denied.');
}

require_once __DIR__ . '/../../core/auth.php';
require_admin();

?>
<script src="../../assets/js/admin.js"></script>

<h2>Admin Dashboard</h2>
<p>Only admins can see this.</p>

