<?php
session_start();

$jsonFile = 'usuaris.json';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Validació de camps buits
if (empty($email) || empty($password)) {
    echo json_encode(['error' => 'Falten camps obligatoris.']);
    exit;
}


$data = json_decode(file_get_contents($jsonFile), true);
if (!is_array($data) || count($data) == 0) {
    echo json_encode(['error' => 'No hi ha usuaris registrats.']);
    exit;
}

// Busquem per email
foreach ($data as $usuari) {
    if (isset($usuari['email']) && $usuari['email'] === $email) {
        // Comprovem la contrasenya
        if (password_verify($password, $usuari['password'])) {
            $_SESSION['user'] = $usuari;
            echo json_encode(['success' => true]); // Resposta correcta
            exit;
        } else {
            echo json_encode(['error' => 'Contrasenya incorrecta.']);
            exit;
        }
    }
}

// Si no es troba el correu electrònic
echo json_encode(['error' => 'Usuari no trobat.']);
exit;
?>
