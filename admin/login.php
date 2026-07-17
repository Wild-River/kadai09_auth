<?php
require_once '../config/session.php';
require_once '../config/db.php';
require_once '../includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        redirect('dashboard.php'); // ①既存のredirect()をそのまま使う
    } else {
        $error = 'ユーザー名またはパスワードが違います';
    }
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <title>ログイン</title>
    <?php require_once '_layout/head.php'; ?>
</head>

<body>
    <h1>ログイン</h1>
    <?php if ($error): ?>
        <p style="color:red;"><?= h($error) ?></p>
    <?php endif; ?>
    <form method="POST" action="login.php">
        <label>ユーザー名: <input type="text" name="username" required></label><br>
        <label>パスワード: <input type="password" name="password" required></label><br>
        <button type="submit">ログイン</button>
    </form>
</body>

</html>