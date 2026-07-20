<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

$sql = "SELECT posts.id, posts.title, posts.slug, posts.status, posts.thumbnail_path, posts.created_at, categories.name AS category_name, GROUP_CONCAT(tags.name SEPARATOR ',') AS tag_names
FROM posts
LEFT JOIN categories ON posts.category_id = categories.id
LEFT JOIN post_tags ON post_tags.post_id = posts.id
LEFT JOIN tags ON tags.id = post_tags.tag_id
WHERE posts.status = 'published'
GROUP BY posts.id
ORDER BY posts.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$posts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <title>ブログ一覧 | Rumi Arakawa -portfolio site-</title>
    <?php require_once '../../admin/_layout/head.php'; ?>
</head>

<body>
    <div class="page-banner">
        <?php require_once '../_layout/nav.php'; ?>

        <h1 class="page-banner__title">blog</h1>
    </div>

    <div class="container">
        <p class="breadcrumb"><a href="/kadai09_auth/public/index.php">TOP</a> / ブログ一覧</p>

        <div class="blog-list">
            <?php foreach ($posts as $post): ?>
                <a class="blog-card" href="single.php?id=<?= h($post['id']) ?>">
                    <?php if (!empty($post['thumbnail_path'])): ?>
                        <span class="thumb-frame">
                            <img class="blog-card__thumb" src="/kadai09_auth/<?= h($post['thumbnail_path']) ?>" alt="<?= h($post['title']) ?>">
                        </span>
                    <?php endif; ?>
                    <div class="blog-card__body">
                        <h2 class="blog-card__title"><?= h($post['title']) ?></h2>
                        <?php if (!empty($post['tag_names'])): ?>
                            <div class="blog-card__tags">
                                <?php foreach (explode(',', $post['tag_names']) as $tagName): ?>
                                    <span class="tag-chip">#<?= h($tagName) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <span class="blog-card__meta">
                            <i class="fa-regular fa-clock"></i>
                            <?= h(date('Y.m.d', strtotime($post['created_at']))) ?>
                        </span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php require_once '../_layout/footer.php'; ?>
</body>

</html>
