<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/github_api.php';
require_once '../config/env.php';

$githubTotals = get_cached_github_languages($pdo, 'Wild-River', getenv('GITHUB_TOKEN'));

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

        <div class="github-langs-section">
            <h2>GitHubリポジトリでの使用言語</h2>
            <?php if (!empty($githubTotals)): ?>
                <?php
                $grandTotal = array_sum($githubTotals);
                $topLangs = array_slice($githubTotals, 0, 6, true);
                $otherBytes = $grandTotal - array_sum($topLangs);
                if ($otherBytes > 0) {
                    $topLangs['その他'] = $otherBytes;
                }

                $colors = ['#67b5b0', '#549691', '#8a9a95', '#c9c2a8', '#b4d9d6', '#3d5c58', '#d9d4c2'];
                ?>
                <div class="github-langs">
                    <div class="github-langs__bar">
                        <?php foreach ($topLangs as $i => $bytes): ?>
                            <?php $percent = $bytes / $grandTotal * 100; ?>
                            <span class="github-langs__segment"
                                style="width: <?= h($percent) ?>%; background: <?= h($colors[array_search($i, array_keys($topLangs)) % count($colors)]) ?>;"></span>
                        <?php endforeach; ?>
                    </div>

                    <ul class="github-langs__list">
                        <?php foreach ($topLangs as $lang => $bytes): ?>
                            <?php
                            $percent = round($bytes / $grandTotal * 100, 1);
                            $colorIndex = array_search($lang, array_keys($topLangs));
                            ?>
                            <li class="github-langs__item">
                                <span class="github-langs__swatch" style="background: <?= h($colors[$colorIndex % count($colors)]) ?>;"></span>
                                <span class="github-langs__name"><?= h($lang) ?></span>
                                <span class="github-langs__percent"><?= h($percent) ?>%</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <?php require_once '_layout/footer.php'; ?>
</body>

</html>