<?php
require_once '../../includes/auth.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET['id'];

    $sql = "SELECT  id, name, slug FROM tags WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $tags = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'];
    $slug = $_POST['slug'];

    $sql = "UPDATE tags SET name = :name, slug = :slug WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_STR);
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);

    try {
        $stmt->execute();
        redirect('index.php');
    } catch (PDOException $e) {
        $error = 'このスラッグは既に使われています';
        $tags = ['id' => $id, 'name' => $name, 'slug' => $slug];
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <title>タグ編集 | ブログ管理システム</title>
    <?php require_once '../_layout/head.php'; ?>
</head>

<body>
    <?php require_once '../_layout/sidebar.php'; ?>

    <div class="container">
        <h1 class="page-title">タグ編集</h1>
        <?php if ($error): ?>
            <p style="color:red;"><?= h($error) ?></p>
        <?php endif; ?>
        <div class="card">
            <form method="post" action="./edit.php" id="new-form">
                <div class="form-group">
                    <label for="name" class="form-label">
                        タグ名
                        <input type="text" id="name" name="name" value="<?= h($tags['name']) ?>" class="form-input" required>
                    </label>
                </div>

                <div class="form-group">
                    <label for="slug" class="form-label">
                        スラッグ
                        <input type="text" id="slug" name="slug" value="<?= h($tags['slug']) ?>" class="form-input" required>
                    </label>
                </div>

                <input type="hidden" name="id" value="<?= h($tags['id']) ?>">
            </form>

            <div class="form-actions">
                <button type="submit" form="new-form" class="submit-btn">変更</button>
                <form method="post" action="delete.php" onsubmit="return confirm('削除しますか？');">
                    <input type="hidden" name="id" value="<?= h($tags['id']) ?>">
                    <button type="submit" class="delete-btn">削除</button>
                </form>

                <a href="index.php" class="back-btn">戻る</a>
            </div>
        </div>
    </div>
</body>

</html>