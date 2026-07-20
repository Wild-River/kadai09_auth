<?php
// 現在開いているページが、どのサイドバーリンクに対応するかを判定してアクティブ表示に使う
// 編集・削除ページのように直接のリンクが無いものは、一覧側のリンクをアクティブにする
$currentPage = $_SERVER['SCRIPT_NAME'];
$sidebarActiveLinks = [
    '/kadai09_auth/admin/dashboard.php'  => ['/kadai09_auth/admin/dashboard.php'],
    '/kadai09_auth/admin/works/index.php'     => ['/kadai09_auth/admin/works/index.php'],
    '/kadai09_auth/admin/works/create.php'     => ['/kadai09_auth/admin/works/create.php'],
    '/kadai09_auth/admin/posts/index.php'     => ['/kadai09_auth/admin/posts/index.php'],
    '/kadai09_auth/admin/posts/create.php'     => ['/kadai09_auth/admin/posts/create.php'],
    '/kadai09_auth/admin/categories/index.php'  => ['/kadai09_auth/admin/categories/index.php'],
    '/kadai09_auth/admin/categories/create.php'  => ['/kadai09_auth/admin/categories/create.php'],
    '/kadai09_auth/admin/tags/index.php'  => ['/kadai09_auth/admin/tags/index.php'],
    '/kadai09_auth/admin/tags/create.php'  => ['/kadai09_auth/admin/tags/create.php']
];

function isSidebarLinkActive(string $href, string $currentPage, array $map): bool {
    return isset($map[$href]) && in_array($currentPage, $map[$href], true);
}
?>

<button type="button" class="menu-toggle" aria-label="メニューを開く" aria-expanded="false" aria-controls="sidebar-nav">
    <span class="menu-toggle__bar"></span>
    <span class="menu-toggle__bar"></span>
    <span class="menu-toggle__bar"></span>
</button>

<aside class="sidebar" id="sidebar">
    <div class="sidebar__brand">
        <a href="/kadai09_auth/admin/dashboard.php">ブログ管理システム</a>
    </div>
    <nav class="sidebar__nav" id="sidebar-nav">
        <div class="sidebar__group">
            <a href="/kadai09_auth/admin/works/index.php" class="sidebar__link<?= isSidebarLinkActive('/kadai09_auth/admin/works/index.php', $currentPage, $sidebarActiveLinks) ? ' is-active' : '' ?>">
                <i class="fa-solid fa-images"></i>
                <span>制作アプリ管理</span>
            </a>
            <a href="/kadai09_auth/admin/works/create.php" class="sidebar__link sidebar__link--sub<?= isSidebarLinkActive('/kadai09_auth/admin/works/create.php', $currentPage, $sidebarActiveLinks) ? ' is-active' : '' ?>">
                <i class="fa-solid fa-plus"></i>
                <span>制作アプリ登録</span>
            </a>
        </div>
        <div class="sidebar__group">
            <a href="/kadai09_auth/admin/posts/index.php" class="sidebar__link<?= isSidebarLinkActive('/kadai09_auth/admin/posts/index.php', $currentPage, $sidebarActiveLinks) ? ' is-active' : '' ?>">
                <i class="fa-solid fa-newspaper"></i>
                <span>ブログ管理</span>
            </a>
            <a href="/kadai09_auth/admin/posts/create.php" class="sidebar__link sidebar__link--sub<?= isSidebarLinkActive('/kadai09_auth/admin/posts/create.php', $currentPage, $sidebarActiveLinks) ? ' is-active' : '' ?>">
                <i class="fa-solid fa-plus"></i>
                <span>ブログ登録</span>
            </a>
        </div>
        <div class="sidebar__group">
            <a href="/kadai09_auth/admin/categories/index.php" class="sidebar__link<?= isSidebarLinkActive('/kadai09_auth/admin/categories/index.php', $currentPage, $sidebarActiveLinks) ? ' is-active' : '' ?>">
                <i class="fa-solid fa-layer-group"></i>
                <span>カテゴリ管理</span>
            </a>
            <a href="/kadai09_auth/admin/categories/create.php" class="sidebar__link sidebar__link--sub<?= isSidebarLinkActive('/kadai09_auth/admin/categories/create.php', $currentPage, $sidebarActiveLinks) ? ' is-active' : '' ?>">
                <i class="fa-solid fa-plus"></i>
                <span>カテゴリ登録</span>
            </a>
        </div>
        <div class="sidebar__group">
            <a href="/kadai09_auth/admin/tags/index.php" class="sidebar__link<?= isSidebarLinkActive('/kadai09_auth/admin/tags/index.php', $currentPage, $sidebarActiveLinks) ? ' is-active' : '' ?>">
                <i class="fa-solid fa-tags"></i>
                <span>タグ管理</span>
            </a>
            <a href="/kadai09_auth/admin/tags/create.php" class="sidebar__link sidebar__link--sub<?= isSidebarLinkActive('/kadai09_auth/admin/tags/create.php', $currentPage, $sidebarActiveLinks) ? ' is-active' : '' ?>">
                <i class="fa-solid fa-plus"></i>
                <span>タグ登録</span>
            </a>
        </div>

    </nav>
    <div class="sidebar__footer">
        <a href="/kadai09_auth/admin/logout.php" class="sidebar__link" title="ログアウト" aria-label="ログアウト">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>ログアウト</span>
        </a>
    </div>
</aside>
<div class="sidebar-overlay"></div>
<script src="/kadai09_auth/assets/js/script.js"></script>