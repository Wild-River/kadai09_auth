<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <?php require_once '_layout/head.php'; ?>
</head>

<body>
    <?php require_once '_layout/sidebar.php'; ?>

    <div class="container">
        <h1 class="page-title">ダッシュボード</h1>

        <div class="card-grid">
            <a href="posts/index.php" class="menu-card">
                <i class="fa-solid fa-newspaper"></i>
                <span>記事管理</span>
            </a>
            <a href="works/index.php" class="menu-card">
                <i class="fa-solid fa-briefcase"></i>
                <span>制作アプリ管理</span>
            </a>
            <a href="categories/index.php" class="menu-card">
                <i class="fa-solid fa-folder"></i>
                <span>カテゴリ管理</span>
            </a>
            <a href="tags/index.php" class="menu-card">
                <i class="fa-solid fa-tag"></i>
                <span>タグ管理</span>
            </a>
        </div>
    </div>
</body>

</html>