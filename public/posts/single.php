<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';
require_once '../../includes/uploads.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET['id'];

    $sql = "SELECT posts.id, posts.title, posts.body, posts.thumbnail_path, posts.slug, posts.status, posts.category_id, posts.created_at, categories.name AS category_name
    FROM posts
    LEFT JOIN categories ON posts.category_id = categories.id
    WHERE posts.id = :id AND status = 'published'";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $post = $stmt->fetch();

    $sql = "SELECT tags.name FROM post_tags JOIN tags ON post_tags.tag_id = tags.id WHERE post_tags.post_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $postTagNames = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <title>記事詳細 | Rumi Arakawa -portfolio site-</title>
    <?php require_once '../../admin/_layout/head.php'; ?>
</head>

<body>
    <div class="page-banner">
        <?php require_once '../_layout/nav.php'; ?>

        <p class="page-banner__title">blog</p>
    </div>

    <div class="container">

        <?php if (!$post): ?>
            <p>記事が見つかりませんでした</p>
            <?php exit(); ?>
        <?php endif; ?>

        <p class="breadcrumb"><a href="/kadai09_auth/public/index.php">TOP</a> / <a href="index.php">記事一覧</a> / <?= h($post['title']) ?></p>

        <article class="article">
            <header class="article-header">
                <h1 class="article-title"><?= h($post['title']) ?></h1>
                <div class="article-meta">
                    <span class="post-list-item__badge"><?= h($post['category_name']) ?></span>
                    <span class="article-meta__date">
                        <i class="fa-regular fa-clock"></i>
                        <?= h(date('Y.m.d', strtotime($post['created_at']))) ?>
                    </span>
                </div>
            </header>

            <?php if (!empty($post['thumbnail_path'])): ?>
                <img class="article-hero" src="/kadai09_auth/<?= h($post['thumbnail_path']) ?>" alt="<?= h($post['title']) ?>">
            <?php endif; ?>

            <div class="article-body"><?= h($post['body']) ?></div>

            <?php if (!empty($postTagNames)): ?>
                <div class="article-tags">
                    <?php foreach ($postTagNames as $tag): ?>
                        <span class="tag-chip">#<?= h($tag) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div>
                <a href="index.php" class="article-back-link"><i class="fa-solid fa-arrow-left"></i> 戻る</a>
            </div>
        </article>

    </div>

    <?php require_once '../_layout/footer.php'; ?>
</body>

</html>