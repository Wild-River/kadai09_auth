<?php
require_once '../../includes/auth.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';
require_once '../../includes/uploads.php';

$statusLabels = statusLabels();

$error = '';
$category_id = '';
$title = '';
$description = '';
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
$uploadDir = __DIR__ . '/../../uploads/works';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = $_SESSION['admin_id'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $slug = $_POST['slug'] ?? '';
    $status = $_POST['status'] ?? '';
    $selectedTagIds = $_POST["tags"] ?? [];

    try {
        $thumbnail_path = 'uploads/works/' . uploadThumbnail($_FILES['thumbnail'], $uploadDir);
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    }

    if (!$error) {
        $sql = "INSERT INTO works (admin_id, category_id, title, description, thumbnail_path, slug, status) VALUES (:admin_id, :category_id, :title, :description, :thumbnail_path, :slug, :status)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':admin_id', $admin_id, PDO::PARAM_STR);
        $stmt->bindValue(':category_id', $category_id, PDO::PARAM_STR);
        $stmt->bindValue(':title', $title, PDO::PARAM_STR);
        $stmt->bindValue(':description', $description, PDO::PARAM_STR);
        $stmt->bindValue(':thumbnail_path', $thumbnail_path, PDO::PARAM_STR);
        $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);

        try {
            $stmt->execute();
            $work_id = $pdo->lastInsertId(); //今INSERTしたばかりの行のIDを取得

            foreach ($_FILES['images']['name'] as $index => $name) {
                $file = [
                    'name'     => $_FILES['images']['name'][$index],
                    'type'     => $_FILES['images']['type'][$index],
                    'tmp_name' => $_FILES['images']['tmp_name'][$index],
                    'error'    => $_FILES['images']['error'][$index],
                    'size'     => $_FILES['images']['size'][$index],
                ];

                try {
                    $file_path = 'uploads/works/' . uploadThumbnail($file, $uploadDir);

                    // ここでwork_imagesにINSERT
                    $sql = "INSERT INTO work_images (work_id, file_path) VALUES (:work_id, :file_path)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':work_id', $work_id, PDO::PARAM_STR);
                    $stmt->bindValue(':file_path', $file_path, PDO::PARAM_STR);
                    $stmt->execute();
                } catch (RuntimeException $e) {
                    $error = '画像のアップロードに失敗しました';
                }
            }

            foreach ($selectedTagIds as $selectedTagId) {
                $sql = "INSERT INTO work_tags (work_id, tag_id) VALUES (:work_id, :tag_id)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':work_id', $work_id, PDO::PARAM_INT);
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
    <title>制作アプリ登録 | ブログ管理システム</title>
    <?php require_once '../_layout/head.php'; ?>
</head>

<body>
    <?php require_once '../_layout/sidebar.php'; ?>

    <div class="container">
        <h1 class="page-title">制作アプリ登録</h1>
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
                    <label for="description" class="form-label">
                        説明文
                        <textarea type="text" id="description" name="description" class="form-input" required><?= h($description) ?></textarea>
                    </label>
                </div>

                <div class="form-group">
                    <label for="thumbnail">
                        サムネイル画像
                        <input type="file" id="thumbnail" name="thumbnail" class="form-input" required>
                    </label>
                </div>

                <div class="form-group">
                    <label for="images" class="form-label">
                        ギャラリー画像（複数選択可）
                        <input type="file" id="images" name="images[]" class="form-input" multiple>
                    </label>
                </div>


                <div class="form-group form-row">
                    <label for="category_id" class="form-label">カテゴリ名</label>
                    <select name="category_id" id="category_id" class="form-input form-input--narrow">
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= h($category["id"]) ?>" <?= $category['id'] == $category_id ? 'selected' : '' ?>><?= h($category["name"]) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group form-row">
                    <span class="form-label">タグ</span>
                    <div class="tag-list">
                        <?php foreach ($tags as $tag): ?>
                            <label>
                                <input type="checkbox" name="tags[]" value="<?= h($tag['id']) ?>"><?= h($tag['name']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group form-row">
                    <label for="slug" class="form-label">スラッグ</label>
                    <input type="text" id="slug" name="slug" value="<?= h($slug) ?>" class="form-input form-input--narrow" required>
                </div>

                <div class="form-group form-row">
                    <label for="status" class="form-label">ステータス</label>
                    <select name="status" id="status" class="form-input form-input--narrow">
                        <?php foreach ($statusLabels as $key => $label): ?>
                            <option value="<?= h($key) ?>" <?= $key === $status ? 'selected' : '' ?>><?= h($label) ?></option>
                        <?php endforeach; ?>
                    </select>
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