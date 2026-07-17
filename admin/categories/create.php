<?php
require_once '../../includes/auth.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $slug = $_POST['slug'] ?? '';

    $sql = "INSERT INTO categories (name, slug) VALUES (:name, :slug)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
    $status = $stmt->execute();

    if (!$status) {
        sql_error($stmt);
    }

    redirect('index.php');
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <title>カテゴリ追加 | ブログ管理システム</title>
    <?php require_once '../_layout/head.php'; ?>
</head>

<body>
    <?php require_once '../_layout/sidebar.php'; ?>

    <div class="container">
        <h1 class="page-title">カテゴリ追加</h1>
        <div class="card">
            <form method="post" action="./create.php" id="new-form">
                <div class="form-group">
                    <label for="name" class="form-label">
                        カテゴリ名
                        <input type="text" id="name" name="name" class="form-input" required>
                    </label>
                </div>

                <div class="form-group">
                    <label for="slug" class="form-label">
                        スラッグ
                        <input type="text" id="slug" name="slug" class="form-input" required>
                    </label>
                </div>
            </form>

            <div class="form-actions">
                <button type="submit" form="new-form" class="submit-btn">登録する</button>
                <a href="index.php" class="back-btn">戻る</a>
            </div>
        </div>
    </div>
</body>

</html>