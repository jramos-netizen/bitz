<?php
session_start();

/**
 * Neteja una cadena rebuda de l'usuari.
 * - Elimina espais extrems
 * - Opcionalment es pot afegir més lògica
 */
function neteja_input(string $data): string {
    $data = trim($data);
    return $data;
}

/**
 * Comprova si l'usuari està autenticat.
 */
function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Redirigeix i atura l'script.
 */
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

/**
 * Escapa text per mostrar-lo en HTML.
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

require_once '/var/www/includes/phpmailer/src/PHPMailer.php';
require_once '/var/www/includes/phpmailer/src/SMTP.php';
require_once '/var/www/includes/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function enviaCorreuValidacio(string $email, string $username, string $token): bool {
    $brevoUser = '9c5b40001@smtp-brevo.com';
    $brevoPass = 'XIaONgCnfrbM85qA';        

    $urlValidacio = 'https://bitz.julioantonio.cat/validateEmail.php?token=' . urlencode($token);

    try {
        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';

        $mail->isSMTP();
        $mail->Host       = 'smtp-relay.brevo.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $brevoUser;
        $mail->Password   = $brevoPass;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('noreply@julioantonio.cat', 'BitZ - Validació eMail');
        $mail->addAddress($email, $username);

        $mail->isHTML(true);
        $mail->Subject = 'Validació del teu compte BitZ';
        $mail->Body    = '
            <p>Hola <b>' . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . '</b>,</p>
            <p>Per completar el registre a <b>BitZ</b>, fes clic en aquest enllaç:</p>
            <p><a href="' . $urlValidacio . '">' . $urlValidacio . '</a></p>
            <p>Si tu no has demanat aquest registre, pots ignorar aquest correu.</p>
        ';
        $mail->AltBody = "Hola $username,\n\nPer validar el teu compte visita:\n$urlValidacio\n\nSi no has demanat aquest registre, ignora aquest correu.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // error_log('Error enviant correu de validació: ' . $e->getMessage());
        return false;
    }
}
