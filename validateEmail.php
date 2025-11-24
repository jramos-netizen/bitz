<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$missatge = '';
$esError  = false;

if (isset($_GET['token'])) {
    $token = neteja_input($_GET['token'] ?? '');

    if ($token === '') {
        $missatge = 'Token de validació no especificat.';
        $esError  = true;
    } else {
        $stmt = $pdo->prepare(
            'SELECT id, username, email, emailValid, verification_expires 
             FROM users 
             WHERE verification_token = ?'
        );
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $missatge = 'Token de validació no vàlid.';
            $esError  = true;
        } else {
            if ((int)$user['emailValid'] === 1) {
                $missatge = 'Aquest correu electrònic ja estava validat prèviament.';
                $esError  = false;
            } else {
                $ara    = time();
                $expira = $user['verification_expires'] ? strtotime($user['verification_expires']) : null;

                if ($expira !== null && $expira < $ara) {
                    // ❗ Token caducat → generar-ne un de nou i reenviar a l’email actual de la BD
                    $nouToken   = bin2hex(random_bytes(32));
                    $nouExpires = (new DateTime('+1 day'))->format('Y-m-d H:i:s');

                    $upd = $pdo->prepare(
                        'UPDATE users 
                         SET verification_token = ?, verification_expires = ? 
                         WHERE id = ?'
                    );
                    $upd->execute([$nouToken, $nouExpires, $user['id']]);

                    if (enviaCorreuValidacio($user['email'], $user['username'], $nouToken)) {
                        $missatge = 'El token de validació havia caducat. T\'hem enviat un nou correu de validació a ' .
                                    htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') . '.';
                        $esError  = true; // Encara no està validat fins que cliqui el nou enllaç
                    } else {
                        $missatge = 'El token ha caducat i hi ha hagut un error enviant un nou correu. Contacta amb el professor.';
                        $esError  = true;
                    }

                } else {
                    // ✅ Token vàlid → marquem emailValid i netegem token
                    $upd = $pdo->prepare(
                        'UPDATE users
                         SET emailValid = 1,
                             verification_token = NULL,
                             verification_expires = NULL
                         WHERE id = ?'
                    );
                    $upd->execute([$user['id']]);

                    $missatge = 'Correu electrònic validat correctament. Ja pots iniciar sessió amb el teu usuari.';
                    $esError  = false;
                }
            }
        }
    }
}
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = neteja_input($_POST['username'] ?? '');
    $email    = neteja_input($_POST['email'] ?? '');

    if ($username === '') {
        $missatge = 'Cal indicar el nom d\'usuari.';
        $esError  = true;
    } elseif ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $missatge = 'Cal indicar un correu electrònic vàlid.';
        $esError  = true;
    } else {
        $stmt = $pdo->prepare(
            'SELECT id, username, email, emailValid 
             FROM users 
             WHERE username = ?'
        );
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $missatge = 'No s\'ha trobat cap usuari amb aquest nom.';
            $esError  = true;
        } elseif ((int)$user['emailValid'] === 1) {
            $missatge = 'Aquest usuari ja té el correu validat. Pots iniciar sessió.';
            $esError  = false;
        } else {            
            // Generem nou token i caducitat, actualitzant possible canvi email
            $nouToken   = bin2hex(random_bytes(32));
            $nouExpires = (new DateTime('+1 day'))->format('Y-m-d H:i:s');

            $upd = $pdo->prepare(
                'UPDATE users
                 SET verification_token = ?, verification_expires = ?, email = ?
                 WHERE id = ?'
            );
            $upd->execute([$nouToken, $nouExpires, $email,  $user['id']]);

            // Enviem correu al email proporcinat
            if (enviaCorreuValidacio($email, $user['username'], $nouToken)) {
                $missatge = 'T\'hem enviat un nou correu de validació a ' .
                            htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '.';
                $esError  = false;
            } else {
                $missatge = 'Hi ha hagut un error enviant el correu.';
                $esError  = true;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Validació de correu</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="container">
        <h1>Validació de correu electrònic</h1>

        <?php if ($missatge): ?>
            <p class="<?php echo $esError ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($missatge, ENT_QUOTES, 'UTF-8'); ?>
            </p>
        <?php endif; ?>

        <p><a href="login.php">Anar a la pàgina d'inici de sessió</a></p>
        <p><a href="index.php">Tornar a l'inici</a></p>

        <hr>

        <h2>No has rebut el correu o vols canviar l'email?</h2>
        <p>Introdueix el teu <b>nom d'usuari</b> i el <b>nou correu electrònic</b>. T'enviarem un nou enllaç de validació.</p>

        <form method="post" action="validateEmail.php">
            <label>
                Usuari:
                <input type="text" name="username" required>
            </label>

            <label>
                Correu electrònic:
                <input type="email" name="email" required>
            </label>

            <button type="submit" class="btn">Reenviar correu de validació</button>
        </form>
    </main>
</body>
</html>
