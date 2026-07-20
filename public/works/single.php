<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';
require_once '../../includes/uploads.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET['id'];

    $sql = "SELECT works.id, works.title, works.description, works.thumbnail_path, works.slug, works.status, works.category_id, works.created_at, categories.name AS category_name
    FROM works
    LEFT JOIN categories ON works.category_id = categories.id
    WHERE works.id = :id AND status = 'published'";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $work = $stmt->fetch();

    $sql = "SELECT tags.name FROM work_tags JOIN tags ON work_tags.tag_id = tags.id WHERE work_tags.work_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $workTagNames = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $sql = "SELECT id, file_path FROM work_images WHERE work_id = :id ORDER BY sort_order";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $workImages = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <title>制作アプリ詳細 | Rumi Arakawa -portfolio site-</title>
    <?php require_once '../../admin/_layout/head.php'; ?>
</head>

<body>
    <div class="page-banner">
        <?php require_once '../_layout/nav.php'; ?>

        <p class="page-banner__title">works</p>
    </div>

    <div class="container">

        <?php if (!$work): ?>
            <p>アプリが見つかりませんでした</p>
            <?php exit(); ?>
        <?php endif; ?>

        <p class="breadcrumb"><a href="/kadai09_auth/public/index.php">TOP</a> / <a href="index.php">制作アプリ詳細</a> / <?= h($work['title']) ?></p>

        <article class="article">
            <header class="article-header">
                <h1 class="article-title"><?= h($work['title']) ?></h1>
                <div class="article-meta">
                    <span class="post-list-item__badge"><?= h($work['category_name']) ?></span>
                    <span class="article-meta__date">
                        <i class="fa-regular fa-clock"></i>
                        <?= h(date('Y.m.d', strtotime($work['created_at']))) ?>
                    </span>
                </div>
            </header>

            <?php if (!empty($work['thumbnail_path'])): ?>
                <img class="article-hero" src="/kadai09_auth/<?= h($work['thumbnail_path']) ?>" alt="<?= h($work['title']) ?>">
            <?php endif; ?>

            <div class="article-body"><?= h($work['description']) ?></div>

            <?php if (!empty($workImages)): ?>
                <div class="article-gallery">
                    <?php foreach ($workImages as $image): ?>
                        <span class="article-gallery__frame">
                            <img src="/kadai09_auth/<?= h($image['file_path']) ?>" alt="">
                        </span>
                    <?php endforeach; ?>
                </div>

                <div class="lightbox" id="lightbox" hidden>
                    <button type="button" class="lightbox__close" aria-label="閉じる">&times;</button>
                    <img class="lightbox__img" src="" alt="">
                </div>
            <?php endif; ?>

            <?php if (!empty($workTagNames)): ?>
                <div class="article-tags">
                    <?php foreach ($workTagNames as $tag): ?>
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

    <?php if (!empty($workImages)): ?>
        <script src="/kadai09_auth/assets/js/lightbox.js"></script>
    <?php endif; ?>
</body>

</html>