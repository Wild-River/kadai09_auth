<?php
// この形式で渡ってくる
// $_FILES['thumbnail'] = [
//     'name'     => 'photo.jpg',       // 元のファイル名（信用しない）
//     'type'     => 'image/jpeg',      // ブラウザ申告のMIME型（これも信用しない、偽装可能）
//     'tmp_name' => '/tmp/phpXXXXXX',  // サーバーの一時保存場所
//     'error'    => 0,                 // 0 = UPLOAD_ERR_OK（成功）
//     'size'     => 123456,            // バイト数
// ];

function uploadThumbnail(array $file, string $uploadDir): ?string {
    // ファイルが選択されていない場合はnullを返す（サムネイル無しでもOKなので）
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    // アップロード自体が失敗している場合
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('アップロードに失敗しました');
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);

    // サイズチェック（例：2MBまで）
    if ($file['size'] > 2 * 1024 * 1024) {
        throw new RuntimeException("画像サイズが大きすぎます。2MB以下の画像をアップロードしてください");
    }

    // 拡張子チェック（jpg/jpeg/png/gif/webpのホワイトリスト）
    $lowerExt = strtolower($ext);
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($lowerExt, $allowed, true)) {
        throw new RuntimeException("拡張子がふさわしくありません");
    }

    // ユニークなファイル名を生成
    $newFileName = uniqid('post_', true) . '.' . $ext;

    // move_uploaded_file()で保存
    $success = move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $newFileName);

    // 保存したパス（例: uploads/posts/xxxxx.jpg）を返す
    if (!$success) {
        throw new RuntimeException('保存に失敗しました');
    }

    return $newFileName;
}
