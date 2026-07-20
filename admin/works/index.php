<?php
require_once '../../includes/auth.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';

$statusLabels = statusLabels();

$sql = "SELECT works.id, works.title, works.slug, works.status, works.thumbnail_path, works.description, categories.name AS category_name FROM works LEFT JOIN categories ON works.category_id = categories.id";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$works = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <title>制作アプリ一覧 | ブログ管理システム</title>
    <?php require_once '../_layout/head.php'; ?>
</head>

<body>
    <?php require_once '../_layout/sidebar.php'; ?>

    <div class="container">
        <div class="page-head">
            <h1>制作アプリ一覧</h1>
            <a href="create.php" class="btn-primary">+ 新規登録</a>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>サムネイル</th>
                        <th>ID</th>
                        <th>タイトル</th>
                        <th>説明文</th>
                        <th>スラッグ</th>
                        <th>ステータス</th>
                        <th>カテゴリ名</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($works as $work): ?>
                        <tr class="row-link" data-href="edit.php?id=<?= h($work['id']) ?>">
                            <td>
                                <?php if (!empty($work['thumbnail_path'])): ?>
                                    <img class="table-thumb" src="/kadai09_auth/<?= h($work['thumbnail_path']) ?>" alt="">
                                <?php endif; ?>
                            </td>
                            <td><?= h($work['id']) ?></td>
                            <td><?= h($work['title']) ?></td>
                            <td class="table-excerpt"><?= h(mb_substr($work['description'], 0, 20)) ?><?= mb_strlen($work['description']) > 20 ? '…' : '' ?></td>
                            <td><?= h($work['slug']) ?></td>
                            <td><?= h($statusLabels[$work['status']]) ?></td>
                            <td><?= h($work['category_name']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="../../assets/js/row-link.js"></script>
</body>

</html>