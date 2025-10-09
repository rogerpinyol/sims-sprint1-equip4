<?php
session_start(); 

$jsonFile = 'usuaris.json';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo "Falten camps obligatoris.";
    exit;
}

if (file_exists($jsonFile)) {
    $data = json_decode(file_get_contents($jsonFile), true);
} else {
    $data = [];
}

$usuariFound = false;
foreach ($data as $usuari) {
    if ($usuari['email'] === $email) {
        if (password_verify($password, $usuari['password'])) {
            $_SESSION['user'] = $usuari; 
            header("Location: main.html");  
            exit;
        } else {
            echo "Contrasenya incorrecta.";
            exit;
        }
    }
}

exit;
?>
