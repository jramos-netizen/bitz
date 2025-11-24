<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$errors = [];
$info   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = neteja_input($_POST['username'] ?? '');
    $password  = $_POST['password'] ?? '';
    $className = $_POST['className'] ?? '';
    $email     = neteja_input($_POST['email'] ?? '');

    // Validacions bàsiques
    if ($username === '') {
        $errors[] = 'Cal indicar un nom d\'usuari.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Cal indicar un correu electrònic vàlid.';
    }
    if ($password === '') {
        $errors[] = 'Cal indicar una contrasenya.';
    }
    if ($className === '') {
        $errors[] = 'Cal seleccionar una classe.';
    }

    if (empty($errors)) {
        // Comprovar si ja existeix l'usuari o l'email
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = 'Ja existeix un usuari amb aquest nom o aquest correu.';
        } else {
            // Crear hash de la contrasenya
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // Generar token de validació i data de caducitat (p.ex. 24h)
            $token   = bin2hex(random_bytes(32));
            $expires = (new DateTime('+1 day'))->format('Y-m-d H:i:s');

            // Inserir nou usuari amb emailValid=0
            $stmt = $pdo->prepare(
                'INSERT INTO users (username, password_hash, className, email, emailValid, verification_token, verification_expires)
                 VALUES (?, ?, ?, ?, 0, ?, ?)'
            );
            $stmt->execute([$username, $hash, $className, $email, $token, $expires]);

            // Enviar correu de validació
            if (enviaCorreuValidacio($email, $username, $token)) {
                $info = 'Registre completat. T\'hem enviat un correu de validació. Revisa la bústia de ' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '.';
            } else {
                $errors[] = 'Usuari creat, però hi ha hagut un problema enviant el correu de validació. Contacta amb el professor.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ca">
    <head>
        <meta charset="UTF-8">
        <title>Registre</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <main class="container">
            <h1>Registre d'usuari</h1>

            <?php if (!empty($errors)): ?>
                <div class="error">
                    <ul>
                        <?php foreach ($errors as $e): ?>
                            <li><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($info): ?>
                <p class="success">
                    <?php echo htmlspecialchars($info, ENT_QUOTES, 'UTF-8'); ?>
                </p>
            <?php endif; ?>

            <form method="post" action="register.php">
                <label>
                    Usuari:
                    <input type="text" name="username" required value="<?php echo htmlspecialchars($username ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </label>

                <label>
                    Correu electrònic:
                    <input type="email" name="email" required value="<?php echo htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </label>

                <label>
                    Classe:
                    <select name="className">
                        <option value="SMX2" <?php echo (isset($className) && $className === 'SMX2') ? 'selected' : ''; ?>>SMX2</option>
                        <option value="ASIX2" <?php echo (isset($className) && $className === 'ASIX2') ? 'selected' : ''; ?>>ASIX2</option>
                    </select>
                </label>

                <label>
                    Contrasenya:
                    <input type="password" name="password" required>
                </label>

                <button type="submit" class="btn">Registrar-se</button>
            </form>

            <p><a href="index.php">Tornar a l'inici</a></p>
        </main>
    </body>
</html>
