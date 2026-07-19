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

    $sql = "SELECT works.id, works.title, works.description, works.thumbnail_path, works.slug, works.status, works.category_id, categories.name AS category_name
    FROM works
    LEFT JOIN categories ON works.category_id = categories.id
    WHERE works.id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $work = $stmt->fetch();

    $sql = "SELECT tag_id FROM work_tags WHERE work_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $workTagIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $sql = "SELECT id, file_path FROM work_images WHERE work_id = :id ORDER BY sort_order";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $workImages = $stmt->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';

    $admin_id = $_SESSION['admin_id'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $slug = $_POST['slug'] ?? '';
    $status = $_POST['status'] ?? '';
    $selectedTagIds = $_POST["tags"] ?? [];

    try {
        $uploadDir = __DIR__ . '/../../uploads/works';
        $newThumbnailPath = uploadThumbnail($_FILES['thumbnail'], $uploadDir);
        $thumbnail_path = $newThumbnailPath ? 'uploads/works/' . $newThumbnailPath : ($_POST['current_thumbnail_path'] ?? '');
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    }

    $sql = "UPDATE works SET admin_id = :admin_id, category_id = :category_id, title = :title, description = :description, thumbnail_path = :thumbnail_path, slug = :slug, status = :status WHERE id = :id";
    $stmt = $pdo->prepare($sql);

    $stmt->bindValue(':id', $id, PDO::PARAM_STR);
    $stmt->bindValue(':admin_id', $admin_id, PDO::PARAM_STR);
    $stmt->bindValue(':category_id', $category_id, PDO::PARAM_STR);
    $stmt->bindValue(':title', $title, PDO::PARAM_STR);
    $stmt->bindValue(':description', $description, PDO::PARAM_STR);
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

        $deleteSql = "DELETE FROM work_tags WHERE work_id = :work_id";
        $deleteStmt = $pdo->prepare($deleteSql);
        $deleteStmt->bindValue(':work_id', $id, PDO::PARAM_INT);
        $deleteStmt->execute();

        foreach ($selectedTagIds as $selectedTagId) {
            $sql = "INSERT INTO work_tags (work_id, tag_id) VALUES (:work_id, :tag_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':work_id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':tag_id', $selectedTagId, PDO::PARAM_INT);
            $stmt->execute();
        }

        // チェックされた画像を削除する
        $deleteImages = $_POST['delete_images'] ?? [];
        foreach ($deleteImages as $deleteImage) {
            $sql = "SELECT file_path FROM work_images WHERE id = :id";
            $imageStmt = $pdo->prepare($sql);
            $imageStmt->bindValue(':id', $deleteImage, PDO::PARAM_INT);
            $imageStmt->execute();
            $image = $imageStmt->fetch();

            $oldThumbnailPath = __DIR__ . '/../../' . $image['file_path'];

            if (file_exists($oldThumbnailPath)) {
                unlink($oldThumbnailPath);
                $deleteSql = "DELETE FROM work_images WHERE id = :id";
                $deleteStmt = $pdo->prepare($deleteSql);
                $deleteStmt->bindValue(':id', $deleteImage, PDO::PARAM_INT);
                $deleteStmt->execute();
            }
        }

        // 新しくアップロードされた画像をwork_imagesに追加する
        foreach ($_FILES['images']['name'] as $index => $name) {
            if ($_FILES['images']['error'][$index] === UPLOAD_ERR_NO_FILE) {
                continue; // ファイルが選択されていない枠はスキップ
            }

            $file = [
                'name'     => $_FILES['images']['name'][$index],
                'type'     => $_FILES['images']['type'][$index],
                'tmp_name' => $_FILES['images']['tmp_name'][$index],
                'error'    => $_FILES['images']['error'][$index],
                'size'     => $_FILES['images']['size'][$index],
            ];

            try {
                $file_path = 'uploads/works/' . uploadThumbnail($file, $uploadDir);

                $sql = "INSERT INTO work_images (work_id, file_path) VALUES (:work_id, :file_path)";
                $newImageStmt = $pdo->prepare($sql);
                $newImageStmt->bindValue(':work_id', $id, PDO::PARAM_STR);
                $newImageStmt->bindValue(':file_path', $file_path, PDO::PARAM_STR);
                $newImageStmt->execute();
            } catch (RuntimeException $e) {
                $error = '画像のアップロードに失敗しました';
            }
        }


        redirect('index.php');
    } catch (PDOException $e) {
        $error = 'このスラッグは既に使われています';
        $work = ['id' => $id, 'category_id' => $category_id, 'title' => $title, 'description' => $description, 'slug' => $slug, 'status' => $status, 'thumbnail_path' => $thumbnail_path];
        $workImages = [];
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <title>作品編集 | ブログ管理システム</title>
    <?php require_once '../_layout/head.php'; ?>
</head>

<body>
    <?php require_once '../_layout/sidebar.php'; ?>

    <div class="container">
        <h1 class="page-title">作品編集</h1>
        <?php if ($error): ?>
            <p style="color:red;"><?= h($error) ?></p>
        <?php endif; ?>
        <div class="card">
            <!-- enctype="multipart/form-data"を追加しないと、ファイルの中身が送信されない -->
            <form method="post" action="./edit.php" enctype="multipart/form-data" id="new-form">

                <div class="form-group">
                    <label for="title" class="form-label">
                        タイトル
                        <input type="text" id="title" name="title" value="<?= h($work['title']) ?>" class="form-input" required>
                    </label>
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">
                        説明文
                        <textarea type="text" id="description" name="description" class="form-input" required><?= h($work['description']) ?></textarea>
                    </label>
                </div>

                <div class="form-group">
                    <label for="thumbnail">
                        サムネイル画像
                        <input type="file" id="thumbnail" name="thumbnail" class="form-input">
                    </label>
                    <?php if (!empty($work['thumbnail_path'])): ?>
                        <img src="/kadai09_auth/<?= h($work['thumbnail_path']) ?>" alt="現在のサムネイル" style="max-width: 200px;">
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    ギャラリー画像
                    <?php foreach ($workImages as $image): ?>
                        <div>
                            <img src="/kadai09_auth/<?= h($image['file_path']) ?>" style="max-width:150px;">
                            <label>
                                <input type="checkbox" name="delete_images[]" value="<?= h($image['id']) ?>">
                                この画像を削除
                            </label>
                        </div>
                    <?php endforeach; ?>

                    <label for="images">画像を追加</label>
                    <input type="file" id="images" name="images[]" class="form-input" multiple>
                </div>

                <div class="form-group">
                    <label for="category_id">
                        カテゴリ名
                        <select name="category_id" id="category_id" class="form-input">
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= h($category["id"]) ?>" <?= $category['id'] == $work['category_id'] ? 'selected' : '' ?>><?= h($category["name"]) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <div class="form-group">
                    タグ
                    <?php foreach ($tags as $tag): ?>
                        <label>
                            <input type="checkbox" name="tags[]" value="<?= h($tag['id']) ?>" <?= in_array($tag['id'], $workTagIds) ? 'checked' : '' ?>><?= h($tag['name']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="form-group">
                    <label for="slug" class="form-label">
                        スラッグ
                        <input type="text" id="slug" name="slug" value="<?= h($work['slug']) ?>" class="form-input" required>
                    </label>
                </div>

                <div class="form-group">
                    <label for="status">
                        ステータス
                        <select name="status" id="status" class="form-input">
                            <?php foreach ($statusLabels as $key => $label): ?>
                                <option value="<?= h($key) ?>" <?= $key == $work['status'] ? 'selected' : '' ?>><?= h($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                </div>

                <input type="hidden" name="id" value="<?= h($work['id']) ?>">
                <input type="hidden" name="current_thumbnail_path" value="<?= h($work['thumbnail_path']) ?>">

            </form>

            <div class="form-actions">
                <button type="submit" form="new-form" class="submit-btn">変更</button>
                <form method="post" action="delete.php" onsubmit="return confirm('削除しますか？');">
                    <input type="hidden" name="id" value="<?= h($work['id']) ?>">
                    <button type="submit" class="delete-btn">削除</button>
                </form>

                <a href="index.php" class="back-btn">戻る</a>
            </div>
        </div>
    </div>
</body>

</html>