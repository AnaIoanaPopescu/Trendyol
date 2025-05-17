<?php
session_start();
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = pdo_connect_mysql();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'], $_POST['comment'], $_POST['rating'])) {
    $user_id = $_SESSION['user_id'];
    $product_id = (int)$_POST['product_id'];
    $comment = htmlspecialchars($_POST['comment']);
    $rating = (int)$_POST['rating'];

    if ($rating < 1 || $rating > 5) {
        exit('Invalid rating value.');
    }

    try {
        $stmt = $pdo->prepare('INSERT INTO reviews (user_id, product_id, rating, comment) VALUES (?, ?, ?, ?)');
        $stmt->execute([$user_id, $product_id, $rating, $comment]);

        header('Location: product.php?id=' . $product_id);
        exit;
    } catch (PDOException $e) {
        exit('Database error: ' . $e->getMessage());
    }
} else {
    header('Location: index.php');
    exit;
}
?>
