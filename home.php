<?php
$stmt = $pdo->prepare('SELECT * FROM products ORDER BY date_added DESC LIMIT 6');
$stmt->execute();
$recently_added_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
template_header('Home')
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css2?family=Satisfy&display=swap" rel="stylesheet">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        
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
       
        .carousel {
            width: 100%;
            height: 400px;
            position: relative;
            overflow: hidden;
        }
        .carousel img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .carousel-inner {
            position: relative;
            width: 100%;
            height: 100%;
        }
        .carousel-item {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1s ease-in-out;
        }
        .carousel-item.active {
            opacity: 1;
        }
        .carousel-controls {
            position: absolute;
            top: 50%;
            width: 100%;
            display: flex;
            justify-content: space-between;
            transform: translateY(-50%);
            z-index: 10;
        }
        .carousel-controls button {
            background: rgba(0, 0, 0, 0.5);
            border: none;
            color: white;
            font-size: 20px;
            padding: 10px;
            cursor: pointer;
        }
        .recentlyadded {
            padding: 20px;
        }
        .recentlyadded h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .products {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }
        .product {
            border: 1px solid #ddd;
            border-radius: 5px;
            text-align: center;
            width: 200px;
            padding: 10px;
            text-decoration: none;
            color: black;
        }
        .product img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        .product .name {
            font-weight: bold;
            margin: 10px 0;
        }
        .product .price {
            color: #28a745;
        }
        .product .rrp {
            text-decoration: line-through;
            color: #999;
        }
    </style>
</head>
<body>

<div id="carousel" class="carousel">
    <div class="carousel-inner">
        <div class="carousel-item active">
            <img src="imgs/carousel1.jpg" alt="Slide 1">
        </div>
        <div class="carousel-item">
            <img src="imgs/carousel2.jpg" alt="Slide 2">
        </div>
        <div class="carousel-item">
            <img src="imgs/carousel3.jpg" alt="Slide 3">
        </div>
    </div>
    <div class="carousel-controls">
        <button id="prevBtn">❮</button>
        <button id="nextBtn">❯</button>
    </div>
</div>

<div class="recentlyadded content-wrapper">
    <h2>Recently added products</h2>
    <div class="products">
        <?php foreach ($recently_added_products as $product): ?>
        <a href="index.php?page=product&id=<?=$product['id']?>" class="product">
            <img src="imgs/<?=$product['img']?>" alt="<?=$product['title']?>">
            <span class="name"><?=$product['title']?></span>
            <span class="price">
                &dollar;<?=$product['price']?>
                <?php if ($product['rrp'] > 0): ?>
                <span class="rrp">&dollar;<?=$product['rrp']?></span>
                <?php endif; ?>
            </span>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<?php
template_footer();
?>


<script>
    const carouselItems = document.querySelectorAll('.carousel-item');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    let currentIndex = 0;

    function showSlide(index) {
        carouselItems.forEach((item, i) => {
            item.classList.toggle('active', i === index);
        });
    }

    function nextSlide() {
        currentIndex = (currentIndex + 1) % carouselItems.length;
        showSlide(currentIndex);
    }

    function prevSlide() {
        currentIndex = (currentIndex - 1 + carouselItems.length) % carouselItems.length;
        showSlide(currentIndex);
    }

    setInterval(nextSlide, 5000);

    nextBtn.addEventListener('click', nextSlide);
    prevBtn.addEventListener('click', prevSlide);
</script>

</body>
</html>
