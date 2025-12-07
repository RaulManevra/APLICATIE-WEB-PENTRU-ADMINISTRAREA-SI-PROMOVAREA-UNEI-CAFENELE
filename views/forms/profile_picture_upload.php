<?php
require_once __DIR__ . '/../../core/csrf.php';
require_once __DIR__ . '/../../core/output.php';
?>
<div class="profile-picture-upload-wrapper">
<form class="profile-picture-upload-box" action="controllers/profile_handler.php" method="POST" enctype="multipart/form-data" autocomplete="on">
    <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
    <h3>Profile picture upload</h3>

    <div class="input-box">
        <input id="profile-picture" type="file" name="profile-picture" accept="image/png, image/jpeg, image/gif" required onchange="document.getElementById('preview-image').src = window.URL.createObjectURL(this.files[0])">
        <div style="margin-top: 10px; text-align: center;">
            <img id="preview-image" src="" alt="Image preview" style="max-width: 200px; max-height: 200px; display: none;" onload="this.style.display='block'">
        </div>
    </div>

    <button type="submit">Upload</button>
</form>
</div>
