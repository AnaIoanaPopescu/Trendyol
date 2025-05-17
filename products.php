<?php
$num_products_on_each_page = 12;
$current_page = isset($_GET['p']) && is_numeric($_GET['p']) ? (int)$_GET['p'] : 1;
$stmt = $pdo->prepare('SELECT * FROM products ORDER BY date_added DESC LIMIT ?,?');

$countries_stmt = $pdo->query('SELECT DISTINCT country_of_origin FROM products ORDER BY country_of_origin ASC');
$countries = $countries_stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt = $pdo->prepare('SELECT * FROM products ORDER BY country_of_origin, date_added DESC LIMIT ?,?');
$selected_country = isset($_GET['country']) ? $_GET['country'] : null;
if ($selected_country) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE country_of_origin = ? ORDER BY date_added DESC LIMIT ?,?');
    $stmt->bindValue(1, $selected_country, PDO::PARAM_STR);
    $stmt->bindValue(2, ($current_page - 1) * $num_products_on_each_page, PDO::PARAM_INT);
    $stmt->bindValue(3, $num_products_on_each_page, PDO::PARAM_INT);
} else {
    $stmt = $pdo->prepare('SELECT * FROM products ORDER BY date_added DESC LIMIT ?,?');
    $stmt->bindValue(1, ($current_page - 1) * $num_products_on_each_page, PDO::PARAM_INT);
    $stmt->bindValue(2, $num_products_on_each_page, PDO::PARAM_INT);
}
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_products_stmt = $pdo->prepare('SELECT COUNT(*) FROM products ' . ($selected_country ? 'WHERE country_of_origin = ?' : ''));
if ($selected_country) {
    $total_products_stmt->bindValue(1, $selected_country, PDO::PARAM_STR);
}
$total_products_stmt->execute();
$total_products = $total_products_stmt->fetchColumn();
?>


<?=template_header('Products')?>

<style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        
        }
 
.products h1 {
    font-family: 'Satisfy', cursive;
    font-size: 70px;
    font-weight: 400;
    color: #394352;
    text-align: center;
    margin-bottom: 20px;
    letter-spacing: 1px;
}

        
        nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
        }
        nav ul li {
            display: inline;
        }
        nav ul li a {
            color: white;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
        }
        nav ul li a:hover {
            background-color: antiquewhite;
        }
       
       

.product:hover {
    transform: translateY(-10px); 
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); 
}

.product img {
    width: 100%; 
    height: 150px; 
    object-fit: cover; 
    border-radius: 8px;
    margin-bottom: 10px; 
}

.product .name {
    font-family: 'Satisfy', cursive; 
    font-size: 20px; 
    color: #394352; 
    margin-bottom: 10px; 
}

.product .price {
    font-family: 'Roboto', sans-serif; 
    font-size: 14px; 
    color: green; 
    font-weight: bold; 
    margin-bottom: 4px;
    display: block;
}

.product .rrp {
    font-family: 'Roboto', sans-serif; 
    font-size: 12px; 
    text-decoration: line-through; 
    color: #999; 
    display: block; 
}
.availability {
    font-size: 14px;
    color: #28a745; 
    margin-top: 5px;
    display: block;
}
.label-promo {
    position: absolute; 
    top: 10px; 
    left: 10px; 
    background-color: #ffc107; 
    color: #000; 
    font-size: 12px; 
    font-weight: bold; 
    padding: 5px 10px; 
    border-radius: 5px; 
    z-index: 10; 
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); 
}

.product {
    position: relative; 
    border: 1px solid #ddd;
    border-radius: 10px;
    text-align: center;
    width: 200px;
    padding: 15px;
    text-decoration: none;
    color: #333;
    background-color: #fff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}


.product {
    position: relative; 
}
    .time-limited {
    font-size: 12px;
    color: #e63946; 
    margin-top: 5px;
    display: block;
}

    .delivery {
    font-size: 13px;
    color: #555; 
    margin-top: 5px;
    display: block;
}
.out-of-stock {
    color: #e63946; 
    font-weight: bold; 
}
    
    .buttons a {
    text-decoration: none;
    color: #555;
    margin: 0 5px;
    padding: 8px 12px;
    background-color: #f8f9fa;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    transition: background-color 0.3s ease;
}

.pagination-container {
    text-align: center; 
    margin-top: 50px; 
}

.buttons a {
    display: inline-flex; 
    justify-content: center; 
    text-decoration: none; 
    color: #555; 
    margin: 0 8px; 
    padding: 12px 20px; 
    background-color: #f8f9fa; 
    border: 1px solid #ddd; 
    border-radius: 5px; 
    font-size: 18px; 
    font-weight: bold;
    transition: background-color 0.3s ease, transform 0.3s ease; 
    text-align: center;
}
    

.buttons a:hover {
    background-color: forestgreen; 
    color: #fff; 
    transform: scale(1.1); 
}

.buttons a[style*="font-weight: bold;"] {
    background-color: forestgreen; 
    color: #fff; 
    font-weight: bold;
    text-decoration: none; 
}

    </style>
<body>
<link href="https://fonts.googleapis.com/css2?family=Satisfy&display=swap" rel="stylesheet">

<div class="products content-wrapper">
    <h1 style="font-size: 60px;">Produits</h1>
    <div class="products-wrapper">
        <?php foreach ($products as $product): ?>
        <a href="index.php?page=product&id=<?=$product['id']?>" class="product">
            
            <?php if (!empty($product['is_promo']) && $product['is_promo'] == 1): ?>
            <div class="label-promo">Reduction</div>
            <?php endif; ?>
            
            <img src="imgs/<?=$product['img']?>" width="200" height="200" alt="<?=$product['title']?>">
            <span class="name"><?=$product['title']?></span>
            <span class="price">
                &dollar;<?=$product['price']?>
            </span>
            <?php if ($product['rrp'] > 0): ?>
            <span class="rrp">&dollar;<?=$product['rrp']?></span>
            <?php endif; ?>
            
        
            <?php if (isset($product['quantity']) && $product['quantity'] > 0): ?>
<span class="availability">In stock (<?=$product['quantity']?> disponibles)</span>
<?php else: ?>
<span class="availability out-of-stock">Out of stock</span>
<?php endif; ?>

            
            <div class="time-limited">Offer valid until: 01/31/2025</div>
            
            <span class="delivery">Delivery: 2-3 days</span>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<form method="get" style="text-align: center; margin-bottom: 20px;">
    <label for="country" style="font-size: 18px; color: #394352;">Filtrer par pays d'origine :</label>
    <select name="country" id="country" style="padding: 5px; font-size: 16px;">
        <option value="">Tous les pays</option>
        <?php foreach ($countries as $country): ?>
            <option value="<?=$country['country_of_origin']?>" <?= $selected_country === $country['country_of_origin'] ? 'selected' : '' ?>>
                <?=$country['country_of_origin']?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit" style="padding: 5px 10px; font-size: 16px;">Apply</button>
</form>
<div class="pagination-container">
    <div class="buttons">
        <?php if ($current_page > 1): ?>
            <a href="index.php?page=products&p=<?=$current_page-1?><?= $recent_filter ? '&recent=1' : '' ?>">Previous</a>
        <?php endif; ?>

        <?php for ($page = 1; $page <= $total_pages; $page++): ?>
            <a href="index.php?page=products&p=<?=$page?><?= $recent_filter ? '&recent=1' : '' ?>" 
               style="padding: 10px 15px; <?= $page == $current_page ? 'font-weight: bold; text-decoration: underline;' : '' ?>">
               <?=$page?>
            </a>
        <?php endfor; ?>

        <?php if ($current_page < $total_pages): ?>
            <a href="index.php?page=products&p=<?=$current_page+1?><?= $recent_filter ? '&recent=1' : '' ?>">Next</a>
        <?php endif; ?>
    </div>
</div>

    <br>
<?php
template_footer();
?>