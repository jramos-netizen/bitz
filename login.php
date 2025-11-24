<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = neteja_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $errors[] = 'Cal indicar usuari i contrasenya.';
    } else {
        // Consulta parametritzada
        $stmt = $pdo->prepare('SELECT id, password_hash, className, emailValid FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Login correcte
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['className'] = $user['className'];
            $_SESSION['username'] = $username;
            if (empty($user['emailValid']) || (int)$user['emailValid'] !== 1) {
                redirect('bitz.php');                
            } else {
                redirect('validateEmail.php');
            }
        } else {
            $errors[] = 'Usuari o contrasenya incorrectes.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ca">
    <head>
        <meta charset="UTF-8">
        <title>Iniciar sessió</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <header>
            <img src="bitz_banner.png" alt="BitZ - Aprendre a Bocinets">
            <h1>Iniciar sessió</h1>
        </header>
        <main>
            <?php if ($errors): ?>
                <div class="alert">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" action="login.php" class="card">
                <label>
                    Nom d'usuari:
                    <input type="text" name="username" required>
                </label>
                <label>
                    Contrasenya:
                    <input type="password" name="password" required>
                </label>
                <button type="submit" class="btn">Entrar</button>
            </form>

            <p><a href="index.php">Tornar a l'inici</a></p>
        </main>
    </body>
</html>


