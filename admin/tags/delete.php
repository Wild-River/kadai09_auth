<?php
require_once '../../includes/auth.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    $sql = "DELETE FROM tags WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    redirect('index.php');
}
