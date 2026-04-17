<?php
require_once '../GENERAL/[General_REQUIRES].php';
require_once '../BBDD/profile_queries.php';

header('Content-Type: application/json');

// Respuesta default para cualquier caso
$response = [
    'success' => false,
    'message' => 'Acción no especificada'
];

// Verificar que se ha recibido una acción
$accion = $_POST['accion'] ?? '';


// Verificar que el usuario está autenticado
$idSesion = (int) ($_SESSION['id_usuario'] ?? 0);
$nicknameSesion = $_SESSION['nickname'] ?? '';

// Si no hay sesión válida, responder con error y salir
if (empty($idSesion) || empty($nicknameSesion)) {
    $response['message'] = 'No autorizado';
    echo json_encode($response);
    exit;
}

// Procesar la acción recibida y generar la respuesta adecuada 
switch ($accion) {
    // Acción para filtrar la biblioteca por categorías 
    case 'filtrar_biblioteca_por_categorias':
        $categoriasStr = $_POST['categorias'] ?? '';
        // Validar que se han recibido categorías
        if (empty($categoriasStr)) {
            $response['message'] = 'No hay categorías especificadas';
            echo json_encode($response);
            exit;
        }

        // Convertir la cadena de categorías en un array de enteros y filtrar valores no válidos
        $categorias = array_map('intval', explode(',', $categoriasStr));
        $categorias = array_filter($categorias, fn($id) => $id > 0);

        // Si después de filtrar no quedan categorías válidas, responder con error
        if (empty($categorias)) {
            $response['message'] = 'Categorías inválidas';
            echo json_encode($response);
            exit;
        }
        // Obtener los juegos de la biblioteca filtrados por las categorías seleccionadas
        $juegos = getLibraryGamesByCategories($BBDD, $idSesion, $nicknameSesion, $categorias);

        $response['success'] = true;
        $response['juegos'] = $juegos;
        break;

    default:
        $response['message'] = 'Acción no reconocida';
}
// Devolver la respuesta en formato JSON
echo json_encode($response);
?>