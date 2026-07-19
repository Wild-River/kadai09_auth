<?php
require_once '../../includes/auth.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    // 記事が削除された時に画像ファイルを削除する
    $sql = "SELECT thumbnail_path FROM posts WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $post = $stmt->fetch();

    $oldThumbnailPath = __DIR__ . '/../../' . $post['thumbnail_path'];

    if (file_exists($oldThumbnailPath)) {
        unlink($oldThumbnailPath);
    }

    $sql = "DELETE FROM posts WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    redirect('index.php');
}
