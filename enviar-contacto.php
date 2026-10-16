<?php
header('Content-Type: application/json; charset=utf-8');

function respond($status, $success, $message)
{
    http_response_code($status);
    echo json_encode([
        'success' => $success,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function post_value($key, $limit)
{
    if (!isset($_POST[$key]) || !is_string($_POST[$key])) {
        return '';
    }

    $value = trim(strip_tags(str_replace("\0", '', $_POST[$key])));
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $limit, 'UTF-8');
    }

    return substr($value, 0, $limit);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, false, 'Método no permitido.');
}

if (post_value('website', 200) !== '') {
    respond(200, true, 'Solicitud enviada. Te contactaremos pronto.');
}

$name = post_value('nombre', 120);
$email = post_value('email', 254);
$phone = post_value('telefono', 60);
$reason = post_value('motivo', 160);
$message = post_value('mensaje', 5000);

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(422, false, 'Ingresa tu nombre y un correo electrónico válido.');
}

$body = implode("\n", [
    'Nombre: ' . $name,
    'Correo electrónico: ' . $email,
    'Teléfono: ' . ($phone !== '' ? $phone : 'No indicado'),
    'Motivo: ' . ($reason !== '' ? $reason : 'No indicado'),
    '',
    'Mensaje:',
    $message !== '' ? $message : 'Sin mensaje adicional'
]);

$headers = [
    'From: GamanZen <contacto@gamanzen.cl>',
    'Reply-To: ' . $email,
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'X-Mailer: PHP/' . phpversion()
];

$sent = mail(
    'contacto@gamanzen.cl',
    'Nueva solicitud desde GamanZen',
    $body,
    implode("\r\n", $headers),
    '-fcontacto@gamanzen.cl'
);

if (!$sent) {
    respond(500, false, 'No se pudo enviar el correo. Inténtalo nuevamente más tarde.');
}

respond(200, true, 'Solicitud enviada. Te contactaremos pronto.');
