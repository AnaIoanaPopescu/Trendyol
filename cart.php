<?php
    if (isset($_POST['product_id'], $_POST['quantity']) && is_numeric($_POST['product_id']) && is_numeric($_POST['quantity'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];

    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product && $quantity > 0) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }

        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $stmt = $pdo->prepare('SELECT * FROM carts WHERE user_id = ? AND product_id = ?');
            $stmt->execute([$user_id, $product_id]);
            $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($cart_item) {
                $stmt = $pdo->prepare('UPDATE carts SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?');
                $stmt->execute([$quantity, $user_id, $product_id]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO carts (user_id, product_id, quantity) VALUES (?, ?, ?)');
                $stmt->execute([$user_id, $product_id, $quantity]);
            }
        }
    }

    header('Location: index.php?page=cart');
    exit;
    }


    if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $product_id = (int)$_GET['remove'];

    unset($_SESSION['cart'][$product_id]);

    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $stmt = $pdo->prepare('DELETE FROM carts WHERE user_id = ? AND product_id = ?');
        $stmt->execute([$user_id, $product_id]);
        }
    }

    if (isset($_GET['remove']) && is_numeric($_GET['remove']) && isset($_SESSION['cart']) && isset($_SESSION['cart'][$_GET['remove']])) {
    unset($_SESSION['cart'][$_GET['remove']]);
    }

    if (isset($_POST['update']) && isset($_SESSION['cart'])) {
    foreach ($_POST as $k => $v) {
        if (strpos($k, 'quantity') !== false && is_numeric($v)) {
            $id = str_replace('quantity-', '', $k);
            $quantity = (int)$v;
            if (is_numeric($id) && isset($_SESSION['cart'][$id]) && $quantity > 0) {
                $_SESSION['cart'][$id] = $quantity;
            }
        }
    }
    header('Location: index.php?page=cart');
    exit;
}

    if (isset($_POST['placeorder']) && isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    try {
        $pdo->beginTransaction();

        $cartItems = $_SESSION['cart'];
        $subtotal = 0;

        foreach ($cartItems as $product_id => $quantity) {
            $stmt = $pdo->prepare('SELECT price, quantity FROM products WHERE id = ?');
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                throw new Exception("Product with ID $product_id not found.");
            }

            if ($product['quantity'] < $quantity) {
                throw new Exception("Insufficient stock for product ID $product_id.");
            }

            $subtotal += $product['price'] * $quantity;

            $stmt = $pdo->prepare('UPDATE products SET quantity = quantity - ? WHERE id = ?');
            $stmt->execute([$quantity, $product_id]);
        }

        $stmt = $pdo->prepare('INSERT INTO orders (user_id, subtotal) VALUES (?, ?)');
        $stmt->execute([$_SESSION['user_id'], $subtotal]);
        $order_id = $pdo->lastInsertId();

        foreach ($cartItems as $product_id => $quantity) {
            $stmt = $pdo->prepare('SELECT price FROM products WHERE id = ?');
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
            $stmt->execute([$order_id, $product_id, $quantity, $product['price']]);
        }

        $pdo->commit();

        $stmt = $pdo->prepare("
            SELECT oi.product_id, oi.quantity, oi.price, p.title
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$order_id]);
        $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $orderDetails = "";
        foreach ($orderItems as $item) {
            $totalPrice = $item['price'] * $item['quantity'];
            $orderDetails .= "{$item['title']} x {$item['quantity']} @ \${$item['price']} each = \${$totalPrice}<br>";
        }

        $userStmt = $pdo->prepare("SELECT email, username AS name FROM users WHERE id = ?");
        $userStmt->execute([$_SESSION['user_id']]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);

        $subject = "SUBJECT: Order confirmation";
        $message = "
        <html>
        <head>
            <title>Order confirmation</title>
        </head>
        <body>
            <h1>Thank you for your order!</h1>
            <p>Hello {$user['name']},</p>
            <p>Here are the details of your order:</p>
            <p>$orderDetails</p>
            <p><strong>Subtotal:</strong> \$$subtotal</p>
            <p>We will inform you once your order has been dispatched.</p>
        </body>
        </html>
        ";

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: your-email@gmail.com" . "\r\n";

        if (mail($user['email'], $subject, $message, $headers)) {
            $emailStatus = '<p>A confirmation e-mail has been sent to your e-mail address.</p>';

            unset($_SESSION['cart']);
            $stmt = $pdo->prepare('DELETE FROM carts WHERE user_id = ?');
            $stmt->execute([$_SESSION['user_id']]);
        } else {
            $emailStatus = '<p>Error sending e-mail.</p>';
        }

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
        exit;
    }
    header('Location: index.php?page=placeorder');
    exit;
    }

$products_in_cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
$products = array();
$subtotal = 0.00;
if ($products_in_cart) {
    $array_to_question_marks = implode(',', array_fill(0, count($products_in_cart), '?'));
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id IN (' . $array_to_question_marks . ')');
    $stmt->execute(array_keys($products_in_cart));
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($products as $product) {
        $subtotal += (float)$product['price'] * (int)$products_in_cart[$product['id']];
    }
}
?>

<?=template_header('Cart')?>
   
    <link href="https://fonts.googleapis.com/css2?family=Satisfy&display=swap" rel="stylesheet">

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

<div class="cart content-wrapper">
   <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
   <link rel="stylesheet" href="style.css">

    <h1>Shopping cart</h1>
    <form action="index.php?page=cart" method="post">
        <table>
            <thead>
                <tr>
                    <td colspan="2">Product</td>
                    <td>Prices</td>
                    <td>Quantity</td>
                    <td>Total</td>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                <tr>
                    <td colspan="5" style="text-align:center;">You have not added any products to your basket.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td class="img">
                        <a href="index.php?page=product&id=<?=$product['id']?>">
                            <img src="imgs/<?=$product['img']?>" width="50" height="50" alt="<?=$product['title']?>">
                        </a>
                    </td>
                    <td>
                        <a href="index.php?page=product&id=<?=$product['id']?>"><?=$product['title']?></a>
                        <br>
                      
                    </td>
                    <td class="price">&dollar;<?=$product['price']?></td>
                    <td class="quantity">
                        <input type="number" name="quantity-<?=$product['id']?>" value="<?=$products_in_cart[$product['id']]?>" min="1" max="<?=$product['quantity']?>" placeholder="Quantity" required>
                        <a href="index.php?page=cart&remove=<?=$product['id']?>" class="remove">
            <i class="fas fa-trash-alt"></i> 
        </a>
                    </td>
                    <td class="price">&dollar;<?=$product['price'] * $products_in_cart[$product['id']]?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="subtotal">
            <span class="text">Subtotal</span>
            <span class="price">&dollar;<?=$subtotal?></span>
        </div>
        <div class="buttons">
            <input type="submit" value="Update" name="update">
            <input type="submit" value="Place Order" name="placeorder">
        </div>
    </form>
</div>

<?=template_footer()?>
