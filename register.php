<?php
$jsonFile = 'usuaris.json';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo "Falten camps obligatoris.";
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
if (file_exists($jsonFile)) {
    $data = json_decode(file_get_contents($jsonFile), true);
} else {
    $data = [];
}

foreach ($data as $usuari) {
    if ($usuari['email'] === $email) {
        echo "Aquest correu ja està registrat.";
        exit;
    }
}

$data[] = [
    'email' => $email,
    'password' => $hashedPassword,
    'data_registre' => date('Y-m-d H:i:s')
];

file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));

header("Location: login.html");
exit;
?>
