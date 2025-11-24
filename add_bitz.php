<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

if (!is_logged_in()) {
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text = neteja_input($_POST['text'] ?? '');

    if ($text !== '') {
        $stmt = $pdo->prepare(
                'INSERT INTO bitz (text, created_by, className) VALUES (?, ?, ?)'
        );
        $stmt->execute([$text, $_SESSION['user_id'], $_SESSION['className']]);
    }
}

redirect('bitz.php');

