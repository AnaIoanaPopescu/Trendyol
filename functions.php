<?php
function pdo_connect_mysql() {
    $DATABASE_HOST = 'localhost';
    $DATABASE_USER = 'root';
    $DATABASE_PASS = '';
    $DATABASE_NAME = 'shoppingcart';
    try {
    	return new PDO('mysql:host=' . $DATABASE_HOST . ';dbname=' . $DATABASE_NAME . ';charset=utf8', $DATABASE_USER, $DATABASE_PASS);
    } catch (PDOException $exception) {
    	exit('Failed to connect to database!');
    }
}

function template_header($title) {
    session_start();
    
    $wishlist_count = 0;

    if (isset($_SESSION['user_id'])) {
        $pdo = pdo_connect_mysql(); 
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM wishlist WHERE user_id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $wishlist_count = $stmt->fetchColumn();
    }
    
    echo <<<EOT
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>$title</title>
		<link href="style.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
	</head>
	<body>
        <header>
            <div class="content-wrapper" style="display: flex; align-items: left; justify-content: space-between;">
                <div style="display: flex; align-items: left;">
                    <!-- Logo -->
                    <img src="imgs/logo1.png" alt="Logo" style="height: 75px; margin-right: 5px; margin-left: -60px;">
                    <!-- Site Name -->
                    <h1 style="margin: 0;">Trendyall</h1>
                </div>
                <!-- Navigation -->
                <nav>
                    <a href="index.php">Home page</a>
                    <a href="index.php?page=products">Shop</a>
EOT;

    if (isset($_SESSION['user_id'])) {
        $username = htmlspecialchars($_SESSION['username']);
        echo <<<EOT
                    <a href="#">Welcome, $username</a>
                    <a href="logout.php">Disconnection</a>
EOT;
    } else {
        echo <<<EOT
                    <a href="login.php">Connection</a>
                    <a href="register.php">Create an account</a>
EOT;
    }
    echo <<<EOT
                    <a href="wishlist.php">Wishlist ($wishlist_count)</a>
EOT;

    echo <<<EOT
                </nav>
                <!-- Cart Icon -->
                <div class="link-icons">
                    <a href="index.php?page=cart">
						<i class="fas fa-shopping-cart"></i>
EOT;

    $num_items_in_cart = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
    echo "<span>$num_items_in_cart</span>";

    echo <<<EOT
					</a>
                </div>
            </div>
        </header>
        <main>
EOT;
}

function template_footer() {
    echo <<<EOT
        </main>
        <footer style="text-align: center; padding: 20px 0; border-top: 1px solid #EEEEEE;">
            <div>
                <p style="margin: 0;">&copy; 2025 <strong>Trendyall</strong>. All rights reserved.</p>
                <p style="margin: 0; font-style: italic;">Your destination for unique style!</p>
                <!-- Logo below the text -->
                <img src="imgs/logo.jpg" alt="Logo" style="height: 120px; margin-top: 10px;">
            </div>
        </footer>
        </body>
        </html>
    EOT;
}

?>
