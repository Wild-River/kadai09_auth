<?php
require_once '../../includes/auth.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';

$statusLabels = statusLabels();

$sql = "SELECT posts.id, posts.title, posts.slug, posts.status, categories.name AS category_name FROM posts LEFT JOIN categories ON posts.category_id = categories.id";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$posts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <title>ブログ一覧 | ブログ管理システム</title>
    <?php require_once '../_layout/head.php'; ?>
</head>

<body>
    <?php require_once '../_layout/sidebar.php'; ?>

    <div class="container">
        <div class="page-head">
            <h1>ブログ一覧</h1>
            <a href="create.php" class="btn-primary">+ 新規登録</a>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>タイトル</th>
                        <th>スラッグ</th>
                        <th>ステータス</th>
                        <th>カテゴリ名</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                        <tr class="row-link" data-href="edit.php?id=<?= h($post['id']) ?>">
                            <td><?= h($post['id']) ?></td>
                            <td><?= h($post['title']) ?></td>
                            <td><?= h($post['slug']) ?></td>
                            <td><?= h($statusLabels[$post['status']]) ?></td>
                            <td><?= h($post['category_name']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="../../assets/js/row-link.js"></script>
</body>

</html>