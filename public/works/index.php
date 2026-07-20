<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

$sql = "SELECT works.id, works.title, works.slug, works.status, works.thumbnail_path, works.created_at, categories.name AS category_name, GROUP_CONCAT(tags.name SEPARATOR ',') AS tag_names
FROM works
LEFT JOIN categories ON works.category_id = categories.id
LEFT JOIN work_tags ON work_tags.work_id = works.id
LEFT JOIN tags ON tags.id = work_tags.tag_id
WHERE works.status = 'published'
GROUP BY works.id
ORDER BY works.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$works = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <title>制作アプリ一覧 | Rumi Arakawa -portfolio site-</title>
    <?php require_once '../../admin/_layout/head.php'; ?>
</head>

<body>
    <div class="page-banner">
        <?php require_once '../_layout/nav.php'; ?>

        <h1 class="page-banner__title">works</h1>
    </div>

    <div class="container">
        <p class="breadcrumb"><a href="/kadai09_auth/public/index.php">TOP</a> / 制作アプリ一覧</p>

        <div class="portfolio-grid">
            <?php foreach ($works as $work): ?>
                <a class="portfolio-card" href="single.php?id=<?= h($work['id']) ?>">
                    <?php if (!empty($work['thumbnail_path'])): ?>
                        <span class="thumb-frame">
                            <img class="portfolio-card__thumb" src="/kadai09_auth/<?= h($work['thumbnail_path']) ?>" alt="<?= h($work['title']) ?>">
                        </span>
                    <?php endif; ?>
                    <div class="portfolio-card__body">
                        <div class="portfolio-card__heading">
                            <h2 class="portfolio-card__title"><?= h($work['title']) ?></h2>
                            <?php if (!empty($work['tag_names'])): ?>
                                <div class="portfolio-card__tags">
                                    <?php foreach (explode(',', $work['tag_names']) as $tagName): ?>
                                        <span class="tag-chip">#<?= h($tagName) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <p class="portfolio-card__category"><?= h($work['category_name']) ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php require_once '../_layout/footer.php'; ?>
</body>

</html>