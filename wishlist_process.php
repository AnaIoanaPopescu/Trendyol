<?php
session_start();
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = pdo_connect_mysql();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $user_id = $_SESSION['user_id'];
    $product_id = (int)$_POST['product_id'];

    try {
        $stmt = $pdo->prepare('SELECT * FROM wishlist WHERE user_id = ? AND product_id = ?');
        $stmt->execute([$user_id, $product_id]);

        if ($stmt->rowCount() === 0) {
            $stmt = $pdo->prepare('INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)');
            $stmt->execute([$user_id, $product_id]);

            header('Location: wishlist.php');
            exit;
        } else {
            header('Location: wishlist.php?message=Product already in wishlist');
            exit;
        }
    } catch (PDOException $e) {
        exit('Database error: ' . $e->getMessage());
    }
} else {
    header('Location: index.php');
    exit;
}
?>
