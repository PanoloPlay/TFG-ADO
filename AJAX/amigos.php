<?php
require_once '../GENERAL/[General_REQUIRES].php';
require_once '../BBDD/amigos_queries.php';

$idSesion = (int) ($_SESSION['id_usuario'] ?? 0);
$nicknameSesion = $_SESSION['nickname'] ?? '';

if (empty($idSesion) || empty($nicknameSesion)) {
    header('Location: ../AUTH/login.php');
    exit;
}

$action = $_POST['action'] ?? '';
$redirect = '../MAIN/friends.php';
$message = '';
$error = '';

switch ($action) {
    case 'add_friend':
        $idAmigo = (int) ($_POST['id_amigo'] ?? 0);
        $nickAmigo = trim($_POST['nick_amigo'] ?? '');

        if ($idAmigo <= 0 || $nickAmigo === '' || $idAmigo === $idSesion) {
            $error = 'No se puede enviar esa solicitud.';
            break;
        }

        if (friendshipExists($BBDD, $idSesion, $idAmigo)) {
            $error = 'Ya existe una relación con ese usuario.';
            break;
        }

        $query = $BBDD->prepare("INSERT INTO Amigos (id_usuario1, nickname1, id_usuario2, nickname2, estado) VALUES (?, ?, ?, ?, 'pendiente')");
        if ($query->execute([$idSesion, $nicknameSesion, $idAmigo, $nickAmigo])) {
            $message = 'Solicitud enviada correctamente.';
        } else {
            $error = 'No se pudo enviar la solicitud. Intenta de nuevo.';
        }
        break;

    case 'accept_request':
        $redirect = '../MAIN/pending-requests.php';
        $idAmistad = (int) ($_POST['id_amistad'] ?? 0);
        if ($idAmistad <= 0) {
            $error = 'Solicitud inválida.';
            break;
        }

        if (acceptFriendRequest($BBDD, $idSesion, $idAmistad)) {
            $message = 'Solicitud aceptada correctamente.';
        } else {
            $error = 'No se pudo aceptar la solicitud.';
        }
        break;

    case 'reject_request':
        $redirect = '../MAIN/pending-requests.php';
        $idAmistad = (int) ($_POST['id_amistad'] ?? 0);
        if ($idAmistad <= 0) {
            $error = 'Solicitud inválida.';
            break;
        }

        if (deleteFriendRequest($BBDD, $idSesion, $idAmistad)) {
            $message = 'Solicitud rechazada correctamente.';
        } else {
            $error = 'No se pudo rechazar la solicitud.';
        }
        break;

    case 'cancel_request':
        $redirect = '../MAIN/pending-requests.php';
        $idAmistad = (int) ($_POST['id_amistad'] ?? 0);
        if ($idAmistad <= 0) {
            $error = 'Solicitud inválida.';
            break;
        }

        if (deleteFriendRequest($BBDD, $idSesion, $idAmistad)) {
            $message = 'Solicitud cancelada correctamente.';
        } else {
            $error = 'No se pudo cancelar la solicitud.';
        }
        break;

    default:
        $error = 'Acción no permitida.';
        break;
}

$params = [];
if ($message !== '') {
    $params['message'] = $message;
}
if ($error !== '') {
    $params['error'] = $error;
}

$location = $redirect;
if (!empty($params)) {
    $location .= '?' . http_build_query($params);
}

header('Location: ' . $location);
exit;
?>
