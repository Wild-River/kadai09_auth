<?php
require_once '../../includes/auth.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';

$sql = "SELECT id,name,slug FROM tags";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$tags = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <title>タグ一覧 | ブログ管理システム</title>
    <?php require_once '../_layout/head.php'; ?>
</head>

<body>
    <?php require_once '../_layout/sidebar.php'; ?>

    <div class="container">
        <div class="page-head">
            <h1>タグ一覧</h1>
            <a href="create.php" class="btn-primary">+ 新規登録</a>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>タグ名</th>
                        <th>スラッグ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tags as $category): ?>
                        <tr class="row-link" data-href="edit.php?id=<?= h($category['id']) ?>">
                            <td><?= h($category['id']) ?></td>
                            <td><?= h($category['name']) ?></td>
                            <td><?= h($category['slug']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="../../assets/js/row-link.js"></script>
</body>

</html>