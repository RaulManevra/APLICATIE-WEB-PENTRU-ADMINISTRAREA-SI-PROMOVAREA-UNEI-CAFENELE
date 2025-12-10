<?php
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit('Direct access denied.');
}
?>
<h2>This is the About page</h2>
                <p>Lorem ipsum dolor sit, amet consectetur adipisicing elit. Laboriosam expedita fugit, eveniet consequuntur, ducimus distinctio voluptatem ratione a qui esse enim ipsa similique vel minus delectus. Neque corrupti animi voluptatum?</p>
                <p>Lorem ipsum dolor sit, amet consectetur adipisicing elit. Laboriosam expedita fugit, eveniet consequuntur, ducimus erwin da pozele a qui esse enim ipsa similique vel minus delectus. Neque corrupti animi voluptatum?</p>
<div class="social-icons">
        <a href="#" class="icon"><i class="fa-brands fa-facebook-f"></i></a>
        <a href="#" class="icon"><i class="fa-brands fa-instagram"></i></a>
    </div>