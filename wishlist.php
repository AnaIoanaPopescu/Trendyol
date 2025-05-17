<?php
session_start();
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$pdo = pdo_connect_mysql();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $stmt = $pdo->prepare('DELETE FROM wishlist WHERE user_id = ? AND product_id = ?');
    $stmt->execute([$user_id, $product_id]);

    header('Location: wishlist.php');
    exit;
}

$stmt = $pdo->prepare('SELECT p.* FROM wishlist w JOIN products p ON w.product_id = p.id WHERE w.user_id = ?');
$stmt->execute([$user_id]);
$wishlist_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?=template_header('Wishlist')?>
<style>
h1 {
    font-family: 'Satisfy', cursive;
    font-size: 25px;
    font-weight: 400;
    color: #394352;
    text-align: center;
    margin-bottom: 20px;
    letter-spacing: 1px;
}
</style>
    <link href="https://fonts.googleapis.com/css2?family=Satisfy&display=swap" rel="stylesheet">

<div class="wishlist-container">
    <h1>Votre liste de souhaits</h1>

    <?php if ($wishlist_items): ?>
        <div class="wishlist-items">
            <?php foreach ($wishlist_items as $item): ?>
                <div class="wishlist-item">
                    <a href="product.php?id=<?=$item['id']?>">
                        <img src="imgs/<?=$item['img']?>" alt="<?=htmlspecialchars($item['title'])?>" />
                    </a>
                    <div class="wishlist-details">
                        <a href="product.php?id=<?=$item['id']?>"><h3><?=htmlspecialchars($item['title'])?></h3></a>
                        <p>Price: $<?=$item['price']?></p>
                        <form action="wishlist.php" method="post">
                            <input type="hidden" name="product_id" value="<?=$item['id']?>">
                            <button type="submit">Supprimer</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Votre liste de souhaits est vide !</p>
    <?php endif; ?>
</div>

<?=template_footer()?>
