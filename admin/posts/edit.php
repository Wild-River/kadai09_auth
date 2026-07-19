<?php
require_once '../../includes/auth.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';
require_once '../../includes/uploads.php';

$statusLabels = statusLabels();

$error = '';

$sql = "SELECT id, name FROM categories";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$categories = $stmt->fetchAll();

$sql = "SELECT id, name FROM tags";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$tags = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET['id'];

    $sql = "SELECT posts.id, posts.title, posts.body, posts.thumbnail_path, posts.slug, posts.status, posts.category_id, categories.name AS category_name
    FROM posts
    LEFT JOIN categories ON posts.category_id = categories.id
    WHERE posts.id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $post = $stmt->fetch();

    $sql = "SELECT tag_id FROM post_tags WHERE post_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $postTagIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';

    $admin_id = $_SESSION['admin_id'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $title = $_POST['title'] ?? '';
    $body = $_POST['body'] ?? '';
    $slug = $_POST['slug'] ?? '';
    $status = $_POST['status'] ?? '';
    $selectedTagIds = $_POST["tags"] ?? [];

    try {
        $uploadDir = __DIR__ . '/../../uploads/posts';
        $newThumbnailPath = uploadThumbnail($_FILES['thumbnail'], $uploadDir);
        $thumbnail_path = $newThumbnailPath ? 'uploads/posts/' . $newThumbnailPath : ($_POST['current_thumbnail_path'] ?? '');
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    }

    $sql = "UPDATE posts SET admin_id = :admin_id, category_id = :category_id, title = :title, body = :body, thumbnail_path = :thumbnail_path, slug = :slug, status = :status WHERE id = :id";
    $stmt = $pdo->prepare($sql);

    $stmt->bindValue(':id', $id, PDO::PARAM_STR);
    $stmt->bindValue(':admin_id', $admin_id, PDO::PARAM_STR);
    $stmt->bindValue(':category_id', $category_id, PDO::PARAM_STR);
    $stmt->bindValue(':title', $title, PDO::PARAM_STR);
    $stmt->bindValue(':body', $body, PDO::PARAM_STR);
    $stmt->bindValue(':thumbnail_path', $thumbnail_path, PDO::PARAM_STR);
    $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
    $stmt->bindValue(':status', $status, PDO::PARAM_STR);

    try {
        $stmt->execute();

        // 新しい画像がアップロードされていれば古いファイルを削除する
        if ($newThumbnailPath) {
            $oldThumbnailPath = __DIR__ . '/../../' . ($_POST['current_thumbnail_path'] ?? '');
            if (file_exists($oldThumbnailPath)) {
                unlink($oldThumbnailPath);
            }
        }

        $deleteSql = "DELETE FROM post_tags WHERE post_id = :post_id";
        $deleteStmt = $pdo->prepare($deleteSql);
        $deleteStmt->bindValue(':post_id', $id, PDO::PARAM_INT);
        $deleteStmt->execute();

        foreach ($selectedTagIds as $selectedTagId) {
            $sql = "INSERT INTO post_tags (post_id, tag_id) VALUES (:post_id, :tag_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':post_id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':tag_id', $selectedTagId, PDO::PARAM_INT);
            $stmt->execute();
        }

        redirect('index.php');
    } catch (PDOException $e) {
        $error = 'このスラッグは既に使われています';
        $post = ['id' => $id, 'category_id' => $category_id, 'title' => $title, 'body' => $body, 'slug' => $slug, 'status' => $status, 'thumbnail_path' => $thumbnail_path];
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <title>ブログ編集 | ブログ管理システム</title>
    <?php require_once '../_layout/head.php'; ?>
</head>

<body>
    <?php require_once '../_layout/sidebar.php'; ?>

    <div class="container">
        <h1 class="page-title">ブログ編集</h1>
        <?php if ($error): ?>
            <p style="color:red;"><?= h($error) ?></p>
        <?php endif; ?>
        <div class="card">
            <!-- enctype="multipart/form-data"を追加しないと、ファイルの中身が送信されない -->
            <form method="post" action="./edit.php" enctype="multipart/form-data" id="new-form">

                <div class="form-group">
                    <label for="title" class="form-label">
                        タイトル
                        <input type="text" id="title" name="title" value="<?= h($post['title']) ?>" class="form-input" required>
                    </label>
                </div>

                <div class="form-group">
                    <label for="body" class="form-label">
                        本文
                        <textarea type="text" id="body" name="body" class="form-input" required><?= h($post['body']) ?></textarea>
                    </label>
                </div>

                <div class="form-group">
                    <label for="thumbnail">
                        サムネイル画像

                        <input type="file" id="thumbnail" name="thumbnail" class="form-input">

                    </label>
                    <?php if (!empty($post['thumbnail_path'])): ?>
                        <img src="/kadai09_auth/<?= h($post['thumbnail_path']) ?>" alt="現在のサムネイル" style="max-width: 200px;">
                    <?php endif; ?>


                </div>

                <div class="form-group">
                    <label for="category_id">
                        カテゴリ名
                        <select name="category_id" id="category_id" class="form-input">
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= h($category["id"]) ?>" <?= $category['id'] == $post['category_id'] ? 'selected' : '' ?>><?= h($category["name"]) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <div class="form-group">
                    タグ
                    <?php foreach ($tags as $tag): ?>
                        <label>
                            <input type="checkbox" name="tags[]" value="<?= h($tag['id']) ?>" <?= in_array($tag['id'], $postTagIds) ? 'checked' : '' ?>><?= h($tag['name']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="form-group">
                    <label for="slug" class="form-label">
                        スラッグ
                        <input type="text" id="slug" name="slug" value="<?= h($post['slug']) ?>" class="form-input" required>
                    </label>
                </div>

                <div class="form-group">
                    <label for="status">
                        ステータス
                        <select name="status" id="status" class="form-input">
                            <?php foreach ($statusLabels as $key => $label): ?>
                                <option value="<?= h($key) ?>" <?= $key == $post['status'] ? 'selected' : '' ?>><?= h($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                </div>

                <input type="hidden" name="id" value="<?= h($post['id']) ?>">
                <input type="hidden" name="current_thumbnail_path" value="<?= h($post['thumbnail_path']) ?>">

            </form>

            <div class="form-actions">
                <button type="submit" form="new-form" class="submit-btn">変更</button>
                <form method="post" action="delete.php" onsubmit="return confirm('削除しますか？');">
                    <input type="hidden" name="id" value="<?= h($post['id']) ?>">
                    <button type="submit" class="delete-btn">削除</button>
                </form>

                <a href="index.php" class="back-btn">戻る</a>
            </div>
        </div>
    </div>
</body>

</html>