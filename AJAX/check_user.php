<?php
require_once "../BBDD/conexion.php";

header('Content-Type: application/json; charset=utf-8');

$field = $_POST['field'] ?? '';
$value = trim($_POST['value'] ?? '');

$allowed = [
    'nickname' => 'nickname',
    'correo' => 'correo'
];

if (!isset($allowed[$field]) || $value === '') {
    echo json_encode(['ok' => false, 'exists' => false]);
    exit;
}

$sql = "SELECT 1 FROM Usuarios WHERE {$allowed[$field]} = ? LIMIT 1";
$stmt = $BBDD->prepare($sql);
$stmt->execute([$value]);

echo json_encode([
    'ok' => true,
    'exists' => (bool)$stmt->fetchColumn()
]);