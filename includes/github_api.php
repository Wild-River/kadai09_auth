<?php
function fetch_github_languages(string $owner, string $repo, string $token): array {
    $url = "https://api.github.com/repos/{$owner}/{$repo}/languages";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer {$token}",
        "Accept: application/vnd.github+json",
        "User-Agent: kadai09-auth-app", // GitHub APIはUser-Agent必須
        "X-GitHub-Api-Version: 2022-11-28",
    ]);

    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($statusCode !== 200) {
        throw new RuntimeException("GitHub API error: HTTP {$statusCode} ({$owner}/{$repo})");
    }

    return json_decode($response, true);
    // 例: ["PHP" => 45000, "JavaScript" => 8000, "CSS" => 3000]
}

function fetch_github_repos(string $username, string $token): array {
    $url = "https://api.github.com/users/{$username}/repos?per_page=100";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer {$token}",
        "Accept: application/vnd.github+json",
        "User-Agent: kadai09-auth-app",
        "X-GitHub-Api-Version: 2022-11-28",
    ]);

    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($statusCode !== 200) {
        throw new RuntimeException("GitHub API error: HTTP {$statusCode} (repos list)");
    }

    $repos = json_decode($response, true);
    return array_column($repos, 'name'); // リポジトリ名の配列
}

function get_cached_github_languages(PDO $pdo, string $username, string $token, int $ttlSeconds = 86400): array {
    $stmt = $pdo->query("SELECT language, bytes, fetched_at FROM github_language_stats ORDER BY bytes DESC");
    $cached = $stmt->fetchAll();

    $isFresh = false;
    if (!empty($cached)) {
        $fetchedAt = strtotime($cached[0]['fetched_at']);
        $isFresh = (time() - $fetchedAt) < $ttlSeconds;
    }

    if ($isFresh) {
        return array_column($cached, 'bytes', 'language');
    }

    try {
        $totals = [];
        $repos = fetch_github_repos($username, $token);
        foreach ($repos as $repo) {
            $languages = fetch_github_languages($username, $repo, $token);
            foreach ($languages as $lang => $bytes) {
                $totals[$lang] = ($totals[$lang] ?? 0) + $bytes;
            }
        }
        arsort($totals);

        $pdo->beginTransaction();
        $pdo->exec("DELETE FROM github_language_stats");
        $insert = $pdo->prepare(
            "INSERT INTO github_language_stats (language, bytes) VALUES (:language, :bytes)"
        );
        foreach ($totals as $lang => $bytes) {
            $insert->execute(['language' => $lang, 'bytes' => $bytes]);
        }
        $pdo->commit();

        return $totals;
    } catch (RuntimeException | PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        // API失敗・DB書き込み失敗時、古いキャッシュがあればそれを返して表示は継続する
        return array_column($cached, 'bytes', 'language');
    }
}
