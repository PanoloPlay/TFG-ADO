<?php
require_once '../GENERAL/[General_REQUIRES].php';
require_once '../BBDD/comment_queries.php';

$gameId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$userNickname = $_SESSION['nickname'] ?? null;
$userLanguages = [
    'id_idioma_principal' => '',
    'id_idioma_secundario' => ''
];

if (!empty($userNickname)) {
    $userLanguages = getUserLanguages($BBDD, $userNickname);
}

$gameData = null;
$comments = [];
$positiveCount = 0;
$negativeCount = 0;

if ($gameId > 0) {
    $gameData = getCommentGameData($BBDD, $gameId);
    if ($gameData) {
        $comments = comment_get_reviews($BBDD, $gameId);
        $positiveCount = comment_get_positive_count($BBDD, $gameId);
        $negativeCount = comment_get_negative_count($BBDD, $gameId);
    }
}
?>

<?php include '../GENERAL/[html_START - head_START].php'; ?>
<script src="../JS/game.js" defer></script>
<link rel="stylesheet" href="../CSS/comment.css">
<?php include '../GENERAL/[head_END - body_START - header - main_START].php'; ?>

<input type="hidden" id="hdnSession" data-value="<?php echo e($userNickname ?? ''); ?>" />
<input type="hidden" id="hdnGameId" data-value="<?php echo e((string)$gameId); ?>" />
<input type="hidden" id="hdnGameName" data-value="<?php echo e($gameData['nombre_juego'] ?? ''); ?>" />
<input type="hidden" id="hdnUserLangPrimary" data-value="<?php echo e($userLanguages['id_idioma_principal'] ?? ''); ?>" />
<input type="hidden" id="hdnUserLangSecondary" data-value="<?php echo e($userLanguages['id_idioma_secundario'] ?? ''); ?>" />

<?php if (!$gameData): ?>
    <section class="game-page">
        <div class="error-message">
            <span class="material-symbols-outlined">videogame_asset_off</span>
            <h2>Juego no encontrado</h2>
            <p>Lo sentimos, el juego que buscas no existe.</p>
            <a class="btn" href="./">Volver al inicio</a>
        </div>
    </section>
<?php else: ?>
    <section class="game-page comment-page">
        <div class="game-main-content">
            <div class="game-title-container">
                <h1 id="big-game-title" class="game-title">Comentarios de <?= e($gameData['nombre_juego']); ?></h1>
                <a href="game.php?id=<?= (int)$gameId; ?>" class="chip chip-soft">
                    Volver al juego
                </a>
            </div>

            <section class="game-comments-panel">
                <div class="section-title">
                    <h2>Reseñas y comentarios</h2>
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <p class="reviews-stats"><?= (int)$positiveCount; ?> positivas · <?= (int)$negativeCount; ?> negativas</p>
                    </div>
                </div>

                <div id="comment-section">
                    <div class="reviews-toolbar">
                        <div class="reviews-toolbar-row d-flex flex-wrap gap-2 align-items-end">
                            <div class="filter-item flex-grow-1" style="min-width: 250px;">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <span class="material-symbols-outlined">search</span>
                                    </span>
                                    <input id="review-search" class="form-control" type="search" placeholder="Buscar en reseñas">
                                </div>
                            </div>

                            <div class="filter-item">
                                <div class="dropdown">
                                    <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center gap-2"
                                            type="button"
                                            id="ratingDropdown"
                                            data-bs-toggle="dropdown">
                                        <span id="selected-rating-icon" class="material-symbols-outlined">star</span>
                                        <span id="selected-rating-text">Todas las valoraciones</span>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="ratingDropdown">
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-2" href="#" data-value="all" data-icon="star">
                                                <span class="material-symbols-outlined">star</span>
                                                Todas las valoraciones
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-2" href="#" data-value="positiva" data-icon="thumb_up">
                                                <span class="material-symbols-outlined text-success">thumb_up</span>
                                                Positivas
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-2" href="#" data-value="negativa" data-icon="thumb_down">
                                                <span class="material-symbols-outlined text-danger">thumb_down</span>
                                                Negativas
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="filter-item">
                                <div class="dropdown">
                                    <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center gap-2"
                                            type="button"
                                            id="languageDropdown"
                                            data-bs-toggle="dropdown">
                                        <span id="selected-language-icon" class="material-symbols-outlined">language</span>
                                        <span id="selected-language-text">Todos los idiomas</span>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="languageDropdown">
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-2" href="#" data-value="all" data-icon="language">
                                                <span class="material-symbols-outlined">language</span>
                                                Todos los idiomas
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-2" href="#" data-value="my" data-icon="translate">
                                                <span class="material-symbols-outlined">translate</span>
                                                Tu idioma
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="filter-item">
                                <div class="dropdown">
                                    <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center gap-2"
                                            type="button"
                                            id="sortDropdown"
                                            data-bs-toggle="dropdown">
                                        <span id="selected-sort-icon" class="material-symbols-outlined">sort</span>
                                        <span id="selected-sort-text">Más recientes</span>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="sortDropdown">
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-2" href="#" data-value="new" data-icon="schedule">
                                                <span class="material-symbols-outlined">schedule</span>
                                                Más recientes
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-2" href="#" data-value="old" data-icon="history">
                                                <span class="material-symbols-outlined">history</span>
                                                Más antiguas
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-2" href="#" data-value="positive" data-icon="thumb_up">
                                                <span class="material-symbols-outlined text-success">thumb_up</span>
                                                Positivas primero
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-2" href="#" data-value="negative" data-icon="thumb_down">
                                                <span class="material-symbols-outlined text-danger">thumb_down</span>
                                                Negativas primero
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="filter-item date-range-wrapper">
                                <button id="review-date-toggle" class="btn btn-outline-light d-flex align-items-center gap-2" type="button">
                                    <span class="material-symbols-outlined">calendar_today</span>
                                    Rango de fecha
                                </button>
                                <div id="date-dropdown" class="date-dropdown mt-2">
                                    <div class="d-flex gap-3">
                                        <div>
                                            <small class="text-muted">Desde:</small>
                                            <input id="review-date-from" type="date" class="form-control">
                                        </div>
                                        <div>
                                            <small class="text-muted">Hasta:</small>
                                            <input id="review-date-to" type="date" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="filter-item filter-reset">
                                <button id="review-reset" class="btn btn-outline-danger d-flex align-items-center gap-2" type="button">
                                    <span class="material-symbols-outlined">refresh</span>
                                    Limpiar
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="comment-list" class="comment-list">
                        <?php if (!empty($comments)): ?>
                            <?php foreach ($comments as $comment): ?>
                                <?php
                                    $commentTimestamp = !empty($comment['fechaPublicacion']) ? strtotime($comment['fechaPublicacion']) : 0;
                                    $commentLanguage = $comment['id_idioma_comentario'] ?? '';
                                    $searchText = strtolower(trim(($comment['nombre_usuario'] ?? '') . ' ' . ($comment['comentario'] ?? '')));
                                    $commentAvatarData = getProfileAvatarData($comment['nickname'] ?? $comment['nombre_usuario']);
                                ?>
                                <div
                                    class="comment comment-card"
                                    data-valoracion="<?php echo e($comment['valoracion']); ?>"
                                    data-idioma="<?php echo e($commentLanguage); ?>"
                                    data-timestamp="<?php echo e((string)$commentTimestamp); ?>"
                                    data-search-text="<?php echo e($searchText); ?>"
                                >
                                    <div class="comment-author-row d-flex align-items-start gap-2 mb-2">
                                        <div class="avatar-32px <?php echo e($commentAvatarData['avatarClass']); ?>"<?php if (!empty($commentAvatarData['avatarPath'])): ?> style="background-image: url('<?php echo e($commentAvatarData['avatarPath']); ?>');"<?php endif; ?>></div>
                                        <div class="comment-author-details">
                                            <p class="mb-1"><strong><?php echo e($comment['nombre_usuario']); ?></strong></p>
                                            <p class="mb-0 comment-meta">Valoración: <span class="rating-<?php echo e($comment['valoracion']); ?>"><?php echo e(ucfirst($comment['valoracion'])); ?></span></p>
                                        </div>
                                    </div>
                                    <div class="comment-body"><?php echo e($comment['comentario']); ?></div>
                                    <p class="comment-date">Fecha: <?php echo e($comment['fechaPublicacion']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="library-alert alert-info">
                                No hay reseñas disponibles para este juego.
                            </div>
                        <?php endif; ?>
                    </div>

                    <p id="no-comment-results" class="no-comment-results" style="display:none;">No se han encontrado reseñas con esos filtros.</p>
                </div>
            </section>
        </div>
    </section>
<?php endif; ?>

<?php include '../GENERAL/[main_END - footer].php'; ?>
<?php include '../GENERAL/[Page_END].php'; ?>