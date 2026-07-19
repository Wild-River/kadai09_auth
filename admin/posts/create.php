<?php
require_once '../../includes/auth.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';
require_once '../../includes/uploads.php';

$statusLabels = statusLabels();

$error = '';
$category_id = '';
$title = '';
$body = '';
$slug = '';
$status = '';

$sql = "SELECT id, name FROM categories";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$categories = $stmt->fetchAll();

$sql = "SELECT id, name FROM tags";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$tags = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = $_SESSION['admin_id'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $title = $_POST['title'] ?? '';
    $body = $_POST['body'] ?? '';
    $slug = $_POST['slug'] ?? '';
    $status = $_POST['status'] ?? '';
    $selectedTagIds = $_POST["tags"] ?? [];

    try {
        $uploadDir = __DIR__ . '/../../uploads/posts';
        $thumbnail_path = 'uploads/posts/' . uploadThumbnail($_FILES['thumbnail'], $uploadDir);
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    }

    if (!$error) {
        $sql = "INSERT INTO posts (admin_id, category_id, title, body, thumbnail_path, slug, status) VALUES (:admin_id, :category_id, :title, :body, :thumbnail_path, :slug, :status)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':admin_id', $admin_id, PDO::PARAM_STR);
        $stmt->bindValue(':category_id', $category_id, PDO::PARAM_STR);
        $stmt->bindValue(':title', $title, PDO::PARAM_STR);
        $stmt->bindValue(':body', $body, PDO::PARAM_STR);
        $stmt->bindValue(':thumbnail_path', $thumbnail_path, PDO::PARAM_STR);
        $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);

        try {
            $stmt->execute();
            $post_id = $pdo->lastInsertId(); //今INSERTしたばかりの行のIDを取得

            foreach ($selectedTagIds as $selectedTagId) {
                $sql = "INSERT INTO post_tags (post_id, tag_id) VALUES (:post_id, :tag_id)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':post_id', $post_id, PDO::PARAM_INT);
                $stmt->bindValue(':tag_id', $selectedTagId, PDO::PARAM_INT);
                $stmt->execute();
            }

            redirect('index.php');
        } catch (PDOException $e) {
            $error = 'このスラッグは既に使われています';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <title>ブログ作成 | ブログ管理システム</title>
    <?php require_once '../_layout/head.php'; ?>
</head>

<body>
    <?php require_once '../_layout/sidebar.php'; ?>

    <div class="container">
        <h1 class="page-title">ブログ作成</h1>
        <?php if ($error): ?>
            <p style="color:red;"><?= h($error) ?></p>
        <?php endif; ?>
        <div class="card">
            <!-- enctype="multipart/form-data"を追加しないと、ファイルの中身が送信されない -->
            <form method="post" action="./create.php" enctype="multipart/form-data" id="new-form">

                <div class="form-group">
                    <label for="title" class="form-label">
                        タイトル
                        <input type="text" id="title" name="title" value="<?= h($title) ?>" class="form-input" required>
                    </label>
                </div>

                <div class="form-group">
                    <label for="body" class="form-label">
                        本文
                        <textarea type="text" id="body" name="body" class="form-input" required><?= h($body) ?></textarea>
                    </label>
                </div>

                <div class="form-group">
                    <label for="thumbnail">
                        サムネイル画像
                        <input type="file" id="thumbnail" name="thumbnail" class="form-input" required>
                    </label>
                </div>

                <div class="form-group">
                    <label for="category_id">
                        カテゴリ名
                        <select name="category_id" id="category_id" class="form-input">
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= h($category["id"]) ?>" <?= $category['id'] == $category_id ? 'selected' : '' ?>><?= h($category["name"]) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <div class="form-group">
                    タグ
                    <?php foreach ($tags as $tag): ?>
                        <label>
                            <input type="checkbox" name="tags[]" value="<?= h($tag['id']) ?>"><?= h($tag['name']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="form-group">
                    <label for="slug" class="form-label">
                        スラッグ
                        <input type="text" id="slug" name="slug" value="<?= h($slug) ?>" class="form-input" required>
                    </label>
                </div>

                <div class="form-group">
                    <label for="status">
                        ステータス
                        <select name="status" id="status" class="form-input">
                            <?php foreach ($statusLabels as $key => $label): ?>
                                <option value="<?= h($key) ?>" <?= $key === $status ? 'selected' : '' ?>><?= h($label) ?></option>
                            <?php endforeach; ?>
                        </select>
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