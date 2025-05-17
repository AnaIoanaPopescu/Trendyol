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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
        $stmt->execute([$username, $email, $password]);
        header('Location: login.php');
        exit;
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            $error = 'Username or email already exists.';
        } else {
            $error = 'An error occurred: ' . $e->getMessage();
        }
    }
}
?>

<?=template_header('Register')?>
<head>
    <link rel="stylesheet" href="style2.css">
    <link href="https://fonts.googleapis.com/css2?family=Satisfy&display=swap" rel="stylesheet">

</head>

<h1>S'inscrire</h1>
<p><?=htmlspecialchars($error)?></p>
<form action="register.php" method="post">
    <label for="username">Nom d'utilisateur:</label>
    <input type="text" id="username" name="username" required>
    <br>
    <label for="email">Adresse e-mail:</label>
    <input type="email" id="email" name="email" required>
    <br>
    <label for="password">Mot de passe:</label>
    <input type="password" id="password" name="password" required>
    <br>
    <input type="submit" value="Register">
</form>

<?=template_footer()?>
