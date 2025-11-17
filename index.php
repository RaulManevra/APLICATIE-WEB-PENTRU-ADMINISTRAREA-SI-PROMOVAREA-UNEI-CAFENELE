<?php
    $page = $_GET['page'] ?? 'home';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php include 'header.php'; ?>
    <?php if($page == 'home'): ?>
    <div class="content-wrapper">
        <div class="bgimg">
            <div class="text">Welcome to Mazi Coffee</div>
        </div>
    </div>
    <?php endif; ?>

    <main>
        <?php
            $file = "include/" . $page . ".php";
            if(file_exists($file)){
                include $file;
            }else{
                include "include/home.php";
            }
         ?>
    </main>
</body>
</html>
