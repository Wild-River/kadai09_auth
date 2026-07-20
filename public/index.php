<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

$sql = "SELECT works.id, works.title, works.slug, works.thumbnail_path, categories.name AS category_name
FROM works
LEFT JOIN categories ON works.category_id = categories.id
WHERE works.status = 'published'
ORDER BY works.created_at DESC
LIMIT 6";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$works = $stmt->fetchAll();

$sql = "SELECT posts.id, posts.title, posts.slug, posts.thumbnail_path, posts.created_at, categories.name AS category_name FROM posts LEFT JOIN categories ON posts.category_id = categories.id WHERE status = 'published' ORDER BY created_at DESC LIMIT 3";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$posts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <title>Rumi Arakawa -portfolio site-</title>
    <?php require_once '../admin/_layout/head.php'; ?>
</head>

<body>
    <section class="hero">
        <?php require_once '_layout/nav.php'; ?>

        <h1 class="hero__title">Rumi Arakawa</h1>
        <p class="hero__subtitle">2026.5月〜 G'sでの学習ログと制作アプリ</p>
    </section>

    <div class="container">
        <div class="hero-overlap">
            <h2>新着記事</h2>
            <div class="post-list">
                <?php foreach ($posts as $post): ?>
                    <a class="post-list-item" href="posts/single.php?id=<?= h($post['id']) ?>">
                        <span class="post-list-item__date"><?= h(date('Y.m.d', strtotime($post['created_at']))) ?></span>
                        <span class="post-list-item__badge"><?= h($post['category_name']) ?></span>
                        <span class="post-list-item__title"><?= h($post['title']) ?></span>
                        <i class="fa-solid fa-chevron-right post-list-item__arrow"></i>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <h2>制作アプリ</h2>
        <div class="portfolio-grid portfolio-grid--home">
            <?php foreach ($works as $work): ?>
                <a class="portfolio-card" href="works/single.php?id=<?= h($work['id']) ?>">
                    <?php if (!empty($work['thumbnail_path'])): ?>
                        <span class="thumb-frame">
                            <img class="portfolio-card__thumb" src="/kadai09_auth/<?= h($work['thumbnail_path']) ?>" alt="<?= h($work['title']) ?>">
                        </span>
                    <?php endif; ?>
                    <div class="portfolio-card__body">
                        <p class="portfolio-card__title"><?= h($work['title']) ?></p>
                        <p class="portfolio-card__category"><?= h($work['category_name']) ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="section-more">
            <a class="btn-primary" href="works/index.php">制作アプリ一覧を見る</a>
        </div>
    </div>

    <?php require_once '_layout/footer.php'; ?>
</body>

</html>