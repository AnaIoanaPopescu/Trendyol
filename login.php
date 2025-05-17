<?php
require_once 'functions.php';

$dsn = 'mysql:host=localhost;dbname=shoppingcart;charset=utf8';
$username = 'root';
$password = '';
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    exit('Database connection failed: ' . $e->getMessage());
}

$error = '';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        $stmt = $pdo->prepare('SELECT * FROM carts WHERE user_id = ?');
        $stmt->execute([$user['id']]);
        $db_cart = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        foreach ($db_cart as $item) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id] += $quantity; 
            } else {
                $_SESSION['cart'][$product_id] = $quantity; 
            }
        }

        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>

<?=template_header('Login')?>
<link href="style3.css" rel="stylesheet" type="text/css">
<link href="https://fonts.googleapis.com/css2?family=Satisfy&display=swap" rel="stylesheet">

<div class="login-container" style="position: relative; display: flex; align-items: center; justify-content: center; z-index: 1;">
    <div style="flex: 1;">
        <h1>Sign in</h1>
        <?php if ($error): ?>
            <p class="error"><?=htmlspecialchars($error)?></p>
        <?php endif; ?>
        <form action="login.php" method="post" style="text-align: center;">
            <label for="username">User name:</label>
            <input type="text" id="username" name="username" required>
            <br>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <br>
            <input type="submit" value="Login">
        </form>
    </div>
</div>

<svg id="pepper-shaker" width="150" height="200" xmlns="http://www.w3.org/2000/svg" style="position: absolute; top: 50%; left: 25%; transform: translate(-50%, -50%); z-index: 0;">
    <g transform="translate(100, 100)">
        <rect x="-30" y="0" width="60" height="100" fill="#6e412c" stroke="#4d2c1a" stroke-width="3" rx="10" />
        <circle cx="0" cy="-20" r="25" fill="#8c5c3a" />
        <circle cx="-10" cy="-20" r="5" fill="#55341f" />
        <circle cx="0" cy="-20" r="5" fill="#55341f" />
        <circle cx="10" cy="-20" r="5" fill="#55341f" />
        <!-- Decorative spices -->
        <circle cx="0" cy="20" r="3" fill="black" />
        <circle cx="15" cy="30" r="3" fill="black" />
        <circle cx="-15" cy="40" r="3" fill="black" />
    </g>
</svg>

<svg id="salt-shaker" width="150" height="200" xmlns="http://www.w3.org/2000/svg" style="position: absolute; top: 50%; right: 5%; transform: translate(50%, -50%); z-index: 0;">
    <g transform="translate(35, 100)">
        <rect x="-30" y="0" width="60" height="100" fill="#ddd" stroke="#bbb" stroke-width="3" rx="10" />
        <circle cx="0" cy="-20" r="25" fill="#ccc" />
        <circle cx="-10" cy="-20" r="5" fill="#aaa" />
        <circle cx="0" cy="-20" r="5" fill="#aaa" />
        <circle cx="10" cy="-20" r="5" fill="#aaa" />
        <!-- Decorative grains -->
        <circle cx="5" cy="30" r="2" fill="white" />
        <circle cx="-10" cy="40" r="2" fill="white" />
        <circle cx="15" cy="50" r="2" fill="white" />
    </g>
</svg>

<script>
    const pepperShaker = document.getElementById('pepper-shaker');
    const saltShaker = document.getElementById('salt-shaker');

    let pepperAngle = 0;
    let saltAngle = 0;
    let pepperDirection = 1;
    let saltDirection = -1;

    function animateShakers() {
        pepperAngle += pepperDirection * 2;
        if (pepperAngle > 10 || pepperAngle < -10) pepperDirection *= -1;
        pepperShaker.setAttribute('style', `position: absolute; top: 50%; left: 5%; transform: translate(-50%, -50%) rotate(${pepperAngle}deg); z-index: 0;`);

        saltAngle += saltDirection * 2;
        if (saltAngle > 10 || saltAngle < -10) saltDirection *= -1;
        saltShaker.setAttribute('style', `position: absolute; top: 50%; right: 5%; transform: translate(50%, -50%) rotate(${saltAngle}deg); z-index: 0;`);

        requestAnimationFrame(animateShakers);
    }

    animateShakers();
</script>
<?=template_footer()?>
