<?php
require_once 'functions.php';
$pdo = pdo_connect_mysql();  

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$_GET['id']]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$product) {
        exit('Product does not exist!');
    }
} else {
    exit('Product does not exist!');
}

if (!isset($_SESSION['recently_viewed'])) {
    $_SESSION['recently_viewed'] = [];
}
if (!in_array($product['id'], $_SESSION['recently_viewed'])) {
    $_SESSION['recently_viewed'][] = $product['id'];
    if (count($_SESSION['recently_viewed']) > 5) {
        array_shift($_SESSION['recently_viewed']);
    }
}

$recent_products = [];
if (!empty($_SESSION['recently_viewed'])) {
    $recent_ids = implode(',', $_SESSION['recently_viewed']);
    $stmt = $pdo->query("SELECT * FROM products WHERE id IN ($recent_ids)");
    $recent_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'created_at';
$order_by = 'created_at DESC';

if ($sort_by === 'rating_desc') {
    $order_by = 'rating DESC'; 
} elseif ($sort_by === 'rating_asc') {
    $order_by = 'rating ASC'; 
}


$stmt = $pdo->prepare("SELECT r.*, u.username FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY $order_by");
$stmt->execute([$product['id']]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>

<?=template_header('Product')?>
<link href="style.css" rel="stylesheet" type="text/css">
<link href="https://fonts.googleapis.com/css2?family=Satisfy&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
<style>

body {
    font-family: 'Roboto', sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f8f9fa;
    color: #555;
}

.product.content-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 50px;
    gap: 50px;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.product img {
    max-width: 100%;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.product .name {
    font-family: 'Satisfy', cursive;
    font-size: 40px;
    color: #394352;
    margin-bottom: 20px;
}

.product .price {
    font-size: 28px;
    color: #28a745;
    font-weight: bold;
    margin-bottom: 10px;
}

.product .rrp {
    font-size: 20px;
    color: #999;
    text-decoration: line-through;
    margin-bottom: 10px;
}

.product .offer {
    font-size: 18px;
    color: #dc3545;
    font-weight: bold;
    margin-bottom: 20px;
}

.product .stock-status {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 20px;
    color: #555;
}

form {
    margin-top: 20px;
}

form input[type="number"] {
    padding: 10px;
    font-size: 16px;
    border: 1px solid #ddd;
    border-radius: 5px;
    width: 150px;
}

form input[type="submit"] {
    padding: 12px 20px;
    font-size: 16px;
    font-weight: bold;
    color: #fff;
    background-color: #007bff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

form input[type="submit"]:hover {
    background-color: #0056b3;
    transform: scale(1.05);
}

.product .description {
    font-size: 16px;
    line-height: 1.5;
    margin-top: 20px;
}

.reviews {
    margin-top: 50px;
    padding: 20px;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.reviews h3 {
    font-size: 28px;
    font-family: 'Satisfy', cursive; 
    color: #394352;
    border-bottom: 2px solid #eee;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

.reviews ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.reviews ul li {
    display: flex;
    flex-direction: column;
    padding: 15px;
    border-bottom: 1px solid #ddd;
    font-size: 16px;
    font-family: 'Roboto', sans-serif;
    line-height: 1.5;
    color: #555;
    background-color: #f8f9fa; 
    border-radius: 5px; 
    margin-bottom: 15px; 
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.reviews ul li:last-child {
    border-bottom: none;
}

.reviews ul li strong {
    font-size: 18px;
    font-weight: bold;
    color: #394352; 
    margin-bottom: 5px;
}


.reviews ul li span {
    font-size: 14px;
    font-weight: bold;
    color: #28a745;
}


.leave-review {
    margin-top: 30px;
    padding: 20px;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); 
}

.leave-review h4 {
    font-size: 24px;
    font-family: 'Satisfy', cursive; 
    color: #394352;
    margin-bottom: 20px;
}

.leave-review-form label {
    font-size: 16px;
    font-weight: bold;
    color: #555;
    margin-bottom: 5px;
    display: block;
}

.leave-review-form textarea,
.leave-review-form select {
    width: 100%;
    padding: 12px;
    font-size: 16px;
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-bottom: 15px;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
}

.leave-review-form textarea {
    height: 100px; 
    resize: vertical; 
}

.leave-review-form input[type="submit"] {
    background-color: #007bff;
    color: #fff;
    font-weight: bold;
    padding: 12px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.leave-review-form input[type="submit"]:hover {
    background-color: #0056b3;
    transform: scale(1.05);
}

.sort-by-container {
    margin-top: 20px;
    padding: 15px;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 10px;
}

.sort-by-container label {
    font-size: 16px;
    font-weight: bold;
    color: #555;
}

.sort-by-container select {
    padding: 8px 15px;
    font-size: 16px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background-color: #f9f9f9;
    color: #333;
    transition: border-color 0.3s ease;
}

.sort-by-container select:hover,
.sort-by-container select:focus {
    border-color: #007bff;
    outline: none;
}

.recent-products {
    margin-top: 50px;
    display: flex;
    gap: 20px;
    overflow-x: auto;
    padding: 10px;
}

.recent-product-item {
    text-align: center;
    flex: 0 0 auto;
    width: 120px;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    padding: 10px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.recent-product-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.recent-product-item img {
    width: 100%;
    height: auto;
    border-radius: 5px;
    margin-bottom: 10px;
}

.recent-product-item p {
    font-size: 14px;
    color: #555;
    margin: 0;
}

</style>
<div class="product content-wrapper">
    <img src="imgs/<?=$product['img']?>" width="500" height="500" alt="<?=htmlspecialchars($product['title'])?>">
    <div>
        <h1 class="name"><?=htmlspecialchars($product['title'])?></h1>
        
        <?php if ($product['quantity'] > 10): ?>
            <p class="stock-status">En stock</p>
        <?php elseif ($product['quantity'] > 0): ?>
            <p class="stock-status">Limited stock - Order quickly!</p>
        <?php else: ?>
            <p class="stock-status">Out of stock</p>
        <?php endif; ?>

        <span class="price">
            &dollar;<?=$product['price']?>
            <?php if ($product['rrp'] > 0): ?>
            <span class="rrp">&dollar;<?=$product['rrp']?></span>
            <p class="offer">
Save <?=round((($product['rrp'] - $product['price']) / $product['rrp']) * 100)?>%!</p>
            <?php endif; ?>
        </span>

        <form action="index.php?page=cart" method="post">
            <input type="number" name="quantity" value="1" min="1" max="<?=$product['quantity']?>" placeholder="Quantity" required>
            <input type="hidden" name="product_id" value="<?=$product['id']?>">
            <input type="submit" value="Add to cart">
        </form>

        <form action="wishlist_process.php" method="post">
            <input type="hidden" name="product_id" value="<?=$product['id']?>">
            <input type="submit" value="Add to wishlist">
        </form>

        <div class="description">
            <?=htmlspecialchars($product['description'])?>
        </div>
    </div>
</div>

<div class="reviews">
    <h3>Customer reviews</h3>
    <ul>
        <?php if (!empty($reviews)): ?>
            <?php foreach ($reviews as $review): ?>
                <li>
                    <strong><?=htmlspecialchars($review['username'])?></strong>:
                    <?=htmlspecialchars($review['comment'])?> 
                    (Rating: <?=$review['rating']?>/5)
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li>No reviews yet. Be the first to leave a review!</li>
        <?php endif; ?>
    </ul>
</div>


<div class="leave-review">
    <h4>Laisser un avis</h4>
    <form action="review_process.php" method="post" class="leave-review-form">
        <label for="comment">Votre avis</label>
        <textarea id="comment" name="comment" placeholder="Write your review..." required></textarea>

        <label for="rating">Note</label>
        <select id="rating" name="rating" required>
            <option value="5">5 - Excellent</option>
            <option value="4">4 - Very good</option>
            <option value="3">3 - Good</option>
            <option value="2">2 - Fair</option>
            <option value="1">1 - Poor</option>
        </select>

        <input type="hidden" name="product_id" value="<?=$product['id']?>">
        <input type="submit" value="Soumettre un avis">
    </form>
</div>

<div class="sort-by-container">
    <form method="get" action="">
        <input type="hidden" name="id" value="<?=$product['id']?>">
        <label for="sort_by">Sort reviews by:</label>
        <select name="sort_by" id="sort_by" onchange="this.form.submit()">
            <option value="created_at" <?=isset($_GET['sort_by']) && $_GET['sort_by'] == 'created_at' ? 'selected' : ''?>>The most recent</option>
            <option value="rating_desc" <?=isset($_GET['sort_by']) && $_GET['sort_by'] == 'rating_desc' ? 'selected' : ''?>>Top grade</option>
            <option value="rating_asc" <?=isset($_GET['sort_by']) && $_GET['sort_by'] == 'rating_asc' ? 'selected' : ''?>>Low rating</option>
        </select>
    </form>
</div>


<h3>Recently viewed products</h3>
<div class="recent-products">
    <?php if (!empty($recent_products)): ?>
        <?php foreach ($recent_products as $recent): ?>
            <div class="recent-product-item">
                <a href="product.php?id=<?=$recent['id']?>">
                    <img src="imgs/<?=$recent['img']?>" alt="<?=htmlspecialchars($recent['title'])?>" width="100" height="100">
                    <p><?=htmlspecialchars($recent['title'])?></p>
                </a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No recently viewed products.</p>
    <?php endif; ?>
</div>


<?=template_footer()?>
