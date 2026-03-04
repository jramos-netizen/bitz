<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';


if (!is_logged_in()) {
    redirect('login.php');
}

// Carreguem els bitz amb el rànquing
$sql = "
    SELECT c.id,
           c.text,
           COALESCE(SUM(v.value), 0) AS ranking
      FROM bitz c
      LEFT JOIN votes v ON c.id = v.bitz_id
     WHERE className = '" . $_SESSION['className'] . "'
     GROUP BY c.id, c.text
     ORDER BY ranking DESC, c.created_at ASC
";

$stmt = $pdo->query($sql);
$bitz = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="ca">
    <head>
        <meta charset="UTF-8">
        <title>BitZ</title>
        <link rel="stylesheet" href="style.css">
        <script defer src="app.js"></script>
    </head>
    <body>
        <header>
            <img src="bitz_banner.png" alt="BitZ - Aprendre a Bocinets">
            <h1>BitZ per aprendre</h1>
            <p>Usuari: <?php echo e($_SESSION['username']); ?> | <?php echo e($_SESSION['className']); ?>   | <a href="logout.php">Tancar sessió</a></p>
        </header>

        <main>
            <section class="card">
                <h2>Afegir nou BitZ</h2>
                <form method="post" action="add_bitz.php">
                    <label>
                        BitZ:
                        <input type="text" name="text" required maxlength="255">
                    </label>
                    <button type="submit" class="btn">Afegir</button>
                </form>
            </section>

            <section>
                <h2>Llista de BitZ</h2>
                <?php if (empty($bitz)): ?>
                    <p>Encara no hi ha BitZ. Afegeix-ne un!</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>BitZ</th>
                                <th>Rànquing</th>
                                <th>Vots</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bitz as $c): ?>
                                <tr data-bitz-id="<?php echo (int) $c['id']; ?>">
                                    <td><?php echo e($c['text']); ?></td>
                                    <td class="ranking"><?php echo (int) $c['ranking']; ?></td>
                                    <td>
                                        <form method="post" action="vote.php" class="inline-form">
                                            <input type="hidden" name="bitz_id" value="<?php echo (int) $c['id']; ?>">
                                            <button type="submit" name="vote" value="up" class="btn btn-small vote-button" data-vote="up">
                                                ▲
                                            </button>
                                            <button type="submit" name="vote" value="down" class="btn btn-small btn-secondary vote-button" data-vote="down">
                                                ▼
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>
        </main>
    </body>
</html>

