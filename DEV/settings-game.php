<?php
require_once '../GENERAL/[General_REQUIRES].php';
require_once '../GENERAL/auth_guard.php';
require_once '../BBDD/settings-game_queries.php';

require_once '../GENERAL/[html_START - head_START].php';
?>
<link rel="stylesheet" href="../CSS/settings-game.css">
<?php
require_once '../GENERAL/[head_END - body_START - header - main_START].php';
?>

<div class="site-main__shell developer-panel-page" style="max-width: 1380px;">
    <header class="developer-panel__hero">
        <div>
            <h1>Panel de Desarrollador</h1>
            <p>Gestiona tus juegos publicados y la configuración de los mismos.</p>
        </div>
    </header>

    <div class="developer-panel__layout">
        <aside class="developer-panel__sidebar">
            <div class="panel-card panel-profile">
                <div class="panel-profile__content">
                    <strong>Panel</strong>
                    <small><?= e($developer['nombre_desarrollador']) ?></small>
                </div>
            </div>

            <nav class="panel-card panel-nav">
                <a class="panel-nav__item <?= $modo === 'listar' ? 'is-active' : '' ?>" href="?modo=listar">
                    <span class="material-symbols-outlined">list</span>
                    Tus juegos
                </a>

                <a class="panel-nav__item <?= $modo === 'nuevo' ? 'is-active' : '' ?>" href="?modo=nuevo">
                    <span class="material-symbols-outlined">add</span>
                    Subir nuevo juego
                </a>

                <?php if ($juegoEdit): ?>
                    <a class="panel-nav__item <?= $modo === 'editar' ? 'is-active' : '' ?>" href="?modo=editar&id=<?= (int) $juegoEdit['id_juego'] ?>">
                        <span class="material-symbols-outlined">edit</span>
                        Editar juego
                    </a>
                <?php else: ?>
                    <span class="panel-nav__item is-disabled" aria-disabled="true">
                        <span class="material-symbols-outlined">edit</span>
                        Editar juego
                    </span>
                <?php endif; ?>

                <?php if ($juegoEdit): ?>
                    <a class="panel-nav__item <?= $modo === 'logros' ? 'is-active' : '' ?>" href="?modo=logros&id=<?= (int) $juegoEdit['id_juego'] ?>">
                        <span class="material-symbols-outlined">emoji_events</span>
                        Logros
                    </a>
                <?php else: ?>
                    <span class="panel-nav__item is-disabled" aria-disabled="true">
                        <span class="material-symbols-outlined">emoji_events</span>
                        Logros
                    </span>
                <?php endif; ?>
            </nav>
        </aside>

        <main class="developer-panel__content">
            <?php if ($mensaje): ?>
                <div class="alert-custom alert-success"><?= e($mensaje) ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert-custom alert-danger"><?= e($error) ?></div>
            <?php endif; ?>

            <?php if ($modo === 'nuevo' || $modo === 'editar'): ?>
                <section class="panel-card">
                    <div class="panel-card__header">
                        <h3 class="panel-card__title"><?= $modo === 'nuevo' ? 'Subir nuevo juego' : 'Editar juego' ?></h3>
                    </div>

                    <form method="post" class="settings-game-form" enctype="multipart/form-data" id="gameForm" data-fallback-image="<?= e($fallbackImageUrl) ?>">
                        <input type="hidden" name="accion" value="guardar_juego">
                        <input type="hidden" name="id_juego" value="<?= $juegoEdit ? (int) $juegoEdit['id_juego'] : '' ?>">

                        <div class="panel-grid panel-grid--2">
                            <div class="form-field">
                                <label class="form-label" for="nombre_juego">Nombre del juego</label>
                                <input type="text" id="nombre_juego" name="nombre_juego" class="form-control" required value="<?= e($juegoEdit['nombre_juego'] ?? '') ?>">
                            </div>

                            <div class="form-field">
                                <label class="form-label" for="fecha_publicacion">Fecha de publicación</label>
                                <input type="datetime-local" id="fecha_publicacion" name="fecha_publicacion" class="form-control" required value="<?= isset($juegoEdit['fecha_publicacion']) ? date('Y-m-d\TH:i', strtotime($juegoEdit['fecha_publicacion'])) : '' ?>">
                            </div>
                        </div>

                        <div class="form-field">
                            <label class="form-label" for="descripcion">Descripción</label>
                            <textarea id="descripcion" name="descripcion" class="form-control description-input" rows="5"><?= e($juegoEdit['descripcion'] ?? '') ?></textarea>
                        </div>

                        <div class="panel-grid panel-grid--2">
                            <div class="form-field">
                                <label class="form-label" for="precio">Precio</label>
                                <input type="text" id="precio" name="precio" class="form-control" placeholder="0.00" value="<?= e((string) ($juegoEdit['precio'] ?? '')) ?>">
                            </div>

                            <div class="form-field">
                                <label class="form-label" for="descuento">Descuento</label>
                                <input type="text" id="descuento" name="descuento" class="form-control" placeholder="0.00" value="<?= e((string) ($juegoEdit['descuento'] ?? '')) ?>">
                            </div>
                        </div>

                        <div class="section-divider">
                            <h5>Imágenes fijas del juego</h5>
                        </div>

                        <div class="panel-grid panel-grid--2">
                            <?php foreach ($staticFields as $variant => $cfg): ?>
                                <div class="panel-card panel-card--sub">
                                    <div class="form-field">
                                        <label class="form-label"><?= e($cfg['label']) ?></label>
                                        <input
                                            type="file"
                                            name="img_<?= e($variant) ?>"
                                            class="form-control js-static-image-input"
                                            accept="image/png,image/jpeg,image/webp,image/x-icon,image/vnd.microsoft.icon"
                                            data-variant="<?= e($variant) ?>"
                                            data-fit="<?= e($cfg['fit']) ?>"
                                            data-crop="<?= $cfg['crop'] ? '1' : '0' ?>"
                                            data-ratio="<?= e($cfg['ratio']) ?>"
                                            data-width="<?= e((string) ($cfg['width'] ?? '')) ?>"
                                            data-height="<?= e((string) ($cfg['height'] ?? '')) ?>"
                                            data-output-type="<?= e((string) ($cfg['type'] ?? 'image/jpeg')) ?>"
                                            data-preview="preview_<?= e($variant) ?>"
                                        >
                                    </div>

                                    <?php
                                    $imgUrl = $juegoEdit
                                        ? getStaticGameImageUrl((int) $juegoEdit['id_juego'], $variant)
                                        : $fallbackImageUrl;
                                    ?>

                                    <div class="media-preview-wrap">
                                        <div
                                            id="preview_<?= e($variant) ?>"
                                            class="media-preview media-preview--<?= e($variant) ?> <?= $cfg['fit'] === 'contain' ? 'media-preview--contain' : '' ?>"
                                            style="background-image: url('<?= e($imgUrl) ?>'); <?= $variant === 'logo' ? 'display:block;' : ''; ?>"
                                        ></div>
                                        <?php
                                        $previewCaption = '';
                                        if (!empty($cfg['width']) && !empty($cfg['height'])) {
                                            $previewCaption = 'Tamaño recomendado: ' . (int) $cfg['width'] . ' x ' . (int) $cfg['height'] . ' px';
                                        } else {
                                            $previewCaption = $variant === 'logo' ? 'Se muestra completa y ajustada al espacio.' : 'Se recorta a su formato estándar.';
                                        }
                                        ?>
                                        <div class="preview-caption">
                                            <?= e($previewCaption) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="section-divider">
                            <h5>Categorías e idiomas</h5>
                        </div>

                        <div class="panel-grid panel-grid--2">
                            <section class="panel-card panel-card--sub">
                                <div class="panel-card__header">
                                    <strong>Categorías del juego</strong>
                                </div>

                                <div class="search-box">
                                    <span class="material-symbols-outlined search-box__icon">search</span>
                                    <input
                                        type="text"
                                        class="form-control js-filter-input"
                                        placeholder="Buscar categoría..."
                                        data-filter-target="categoriesList"
                                    >
                                    <button
                                        type="button"
                                        class="btn-icon js-clear-filter"
                                        data-filter-target="categoriesList"
                                        title="Limpiar búsqueda"
                                    >
                                        <span class="material-symbols-outlined">refresh</span>
                                    </button>
                                </div>

                                <div class="option-list option-list--scroll scrollbar-theme" id="categoriesList">
                                    <?php foreach ($categorias as $categoria): ?>
                                        <?php
                                        $categoriaId = (int) $categoria['id_categoria'];
                                        $checked = in_array($categoriaId, $categoriasSeleccionadas, true);
                                        ?>
                                        <label class="option-item js-filter-item checkbox-filter">
                                            <input
                                                type="checkbox"
                                                name="categorias[]"
                                                value="<?= $categoriaId ?>"
                                                <?= $checked ? 'checked' : '' ?>
                                            >
                                            <span><?= e($categoria['categoria']) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </section>

                            <section class="panel-card panel-card--sub">
                                <div class="panel-card__header">
                                    <strong>Idiomas disponibles</strong>
                                </div>

                                <div class="search-box">
                                    <span class="material-symbols-outlined search-box__icon">search</span>
                                    <input
                                        type="text"
                                        class="form-control js-filter-input"
                                        placeholder="Buscar idioma..."
                                        data-filter-target="languagesList"
                                    >
                                    <button
                                        type="button"
                                        class="btn-icon js-clear-filter"
                                        data-filter-target="languagesList"
                                        title="Limpiar búsqueda"
                                    >
                                        <span class="material-symbols-outlined">refresh</span>
                                    </button>
                                </div>

                                <div class="option-list option-list--scroll scrollbar-theme" id="languagesList">
                                    <?php foreach ($idiomasJuego as $idioma): ?>
                                        <?php
                                        $idiomaId = (string) $idioma['id_idioma'];
                                        $checked = in_array($idiomaId, $idiomasSeleccionados, true);
                                        ?>
                                        <label class="option-item js-filter-item checkbox-filter">
                                            <input
                                                type="checkbox"
                                                name="idiomas[]"
                                                value="<?= e($idiomaId) ?>"
                                                <?= $checked ? 'checked' : '' ?>
                                            >
                                            <span><?= e($idioma['idioma']) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </section>
                        </div>

                        <div class="section-divider">
                            <div class="section-divider__row">
                                <h5 class="mb-0">Multimedia del carrusel</h5>
                                <button type="button" class="btn-primary btn-sm" id="addCarouselItem">
                                    <span class="material-symbols-outlined icon-add">add</span>
                                    Añadir objeto
                                </button>
                            </div>
                            <p class="helper-text">Arrastra para ordenar. Las imágenes se recortan a 16:9 antes de subirlas.</p>
                        </div>

                        <div id="carouselList" class="carousel-list">
                            <?php for ($i = 0; $i < 3; $i++): ?>
                                <div class="carousel-item-row">
                                    <div class="carousel-item__handle">
                                        <button type="button" class="btn-drag" title="Arrastrar">
                                            <span class="material-symbols-outlined">reorder</span>
                                        </button>
                                    </div>

                                    <div class="carousel-item__preview js-carousel-image-preview" style="background-image: url('<?= e($fallbackImageUrl) ?>');"></div>
                                    <video class="carousel-item__video js-carousel-video-preview" controls style="display:none;"></video>

                                    <div class="carousel-item__body">
                                        <div class="carousel-item__fields">
                                            <div class="carousel-item__field carousel-item__field--file">
                                                <label class="form-label mb-1">Archivo</label>
                                                <input
                                                    type="file"
                                                    name="carousel_files[]"
                                                    class="form-control js-carousel-file"
                                                    accept="image/png,image/jpeg,image/webp,video/mp4,video/webm,video/ogg"
                                                >
                                            </div>

                                            <div class="carousel-item__field carousel-item__field--order">
                                                <label class="form-label mb-1">Orden</label>
                                                <input
                                                    type="number"
                                                    name="carousel_orders[]"
                                                    class="carousel-order-input js-carousel-order"
                                                    min="1"
                                                    value="<?= $i + 1 ?>"
                                                >
                                            </div>

                                            <div class="carousel-item__field carousel-item__field--type">
                                                <label class="form-label mb-1">Tipo</label>
                                                <select name="carousel_types[]" class="form-control js-carousel-type">
                                                    <option value="">Auto</option>
                                                    <option value="imagen">Imagen</option>
                                                    <option value="video">Vídeo</option>
                                                </select>
                                            </div>

                                            <button type="button" class="btn-danger btn--icon js-remove-carousel-item">
                                                <span class="material-symbols-outlined">delete</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>

                        <div class="form-actions">
                            <button class="btn-primary btn-md" type="submit">
                                <span class="material-symbols-outlined icon-save">save</span>
                                Guardar
                            </button>
                            <a href="?modo=listar" class="btn-secondary btn-md">
                                <span class="material-symbols-outlined icon-cancel">cancel</span>
                                Cancelar
                            </a>
                        </div>
                    </form>
                </section>
            <?php endif; ?>

            <?php if ($juegoEdit && $modo !== 'logros'): ?>
                <section class="panel-card">
                    <div class="panel-card__header">
                        <strong>Multimedia actual del juego</strong>
                    </div>

                    <?php if (!empty($multimediaEdit)): ?>
                        <div class="media-grid">
                            <?php foreach ($multimediaEdit as $media): ?>
                                <div class="media-card">
                                    <div class="small text-muted mb-1">
                                        Orden: <?= (int) $media['numero_orden'] ?> · <?= e($media['tipo']) ?>
                                    </div>

                                    <?php if ($media['tipo'] === 'imagen'): ?>
                                        <img src="<?= e($media['url_multimedia']) ?>" class="multimedia-media" alt="multimedia">
                                    <?php else: ?>
                                        <video class="multimedia-media" controls>
                                            <source src="<?= e(getGameVideoUrl((int) $media['id_juego'], basename((string) $media['url_multimedia']))) ?>">
                                        </video>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="media-preview media-preview--carousel media-preview--contain" style="background-image: url('<?= e($fallbackImageUrl) ?>');"></div>
                            <div class="empty-state__text">No hay multimedia cargada para el carrusel.</div>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

            <?php if ($modo === 'logros' && $juegoEdit): ?>
                <section class="panel-card">
                    <div class="panel-card__header">
                        <h3 class="panel-card__title">Gestionar Logros - <?= e($juegoEdit['nombre_juego']) ?></h3>
                    </div>

                    <div class="section-achievement-preview">
                        <h5>Vista previa de logros</h5>
                    </div>

                    <div class="achievement-preview-row">
                        <div class="achievement-preview-row-title">Conseguido</div>
                        <div class="achievement-preview-grid">
                            <?php foreach (['cobre', 'plata', 'oro', 'platino', 'lotus'] as $rareza): ?>
                                <div class="achievement-preview-card">
                                    <img
                                        src="../MEDIA/IMG/juegos/fallback/achivements/<?= e($rareza) ?>.jpg"
                                        alt="Ejemplo logro <?= e($rareza) ?> conseguido"
                                        class="achievement-preview-image"
                                    >
                                    <div class="small text-muted mt-1"><?= ucfirst($rareza) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="achievement-preview-row">
                        <div class="achievement-preview-row-title">No conseguido</div>
                        <div class="achievement-preview-grid achievement-preview-grayscale">
                            <?php foreach (['cobre', 'plata', 'oro', 'platino', 'lotus'] as $rareza): ?>
                                <div class="achievement-preview-card">
                                    <img
                                        src="../MEDIA/IMG/juegos/fallback/achivements/<?= e($rareza) ?>.jpg"
                                        alt="Ejemplo logro <?= e($rareza) ?> no conseguido"
                                        class="achievement-preview-image"
                                    >
                                    <div class="small text-muted mt-1"><?= ucfirst($rareza) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <form method="post" class="achievements-form" id="achievementsForm" enctype="multipart/form-data">
                        <input type="hidden" name="accion" value="guardar_logros">
                        <input type="hidden" name="id_juego" value="<?= (int) $juegoEdit['id_juego'] ?>">

                        <div class="form-field">
                            <button type="button" class="btn-primary btn-sm" id="addAchievementItem">
                                <span class="material-symbols-outlined">add</span>
                                Agregar logro
                            </button>
                        </div>

                        <div id="achievementsList" class="achievement-list">
                            <?php if (!empty($logrosJuego)): ?>
                                <?php foreach ($logrosJuego as $index => $logro): ?>
                                    <div class="achievement-item-row" data-logro-id="<?= (int) $logro['id_logro'] ?>" data-index="<?= $index ?>">
                                        <div class="achievement-item__body">
                                            <div class="achievement-item__fields">
                                                <div class="achievement-item__field achievement-item__field--name">
                                                    <label class="form-label">Nombre</label>
                                                    <input
                                                        type="text"
                                                        name="achievement_names[]"
                                                        class="form-control js-achievement-name"
                                                        placeholder="Nombre del logro"
                                                        value="<?= e($logro['nombre_logro']) ?>"
                                                        required
                                                    >
                                                </div>

                                                <div class="achievement-item__field achievement-item__field--description">
                                                    <label class="form-label">Descripción</label>
                                                    <input
                                                        type="text"
                                                        name="achievement_descriptions[]"
                                                        class="form-control js-achievement-description"
                                                        placeholder="Descripción del logro"
                                                        value="<?= e($logro['descripcion_logro']) ?>"
                                                    >
                                                </div>

                                                <div class="achievement-item__field achievement-item__field--rarity">
                                                    <label class="form-label">Rareza</label>
                                                    <select
                                                        name="achievement_rarities[]"
                                                        class="form-control js-achievement-rarity"
                                                        required
                                                    >
                                                        <option value="cobre" <?= $logro['rareza'] === 'cobre' ? 'selected' : '' ?>>Cobre</option>
                                                        <option value="plata" <?= $logro['rareza'] === 'plata' ? 'selected' : '' ?>>Plata</option>
                                                        <option value="oro" <?= $logro['rareza'] === 'oro' ? 'selected' : '' ?>>Oro</option>
                                                        <option value="platino" <?= $logro['rareza'] === 'platino' ? 'selected' : '' ?>>Platino</option>
                                                        <option value="lotus" <?= $logro['rareza'] === 'lotus' ? 'selected' : '' ?>>Lotus</option>
                                                    </select>
                                                </div>

                                                <div class="achievement-item__field achievement-item__field--identifier">
                                                    <label class="form-label">ID Único</label>
                                                    <input
                                                        type="text"
                                                        class="form-control js-achievement-id"
                                                        value="<?= e($logro['identificador_unico']) ?>"
                                                        readonly
                                                    >
                                                </div>

                                                <input type="hidden" name="achievement_ids[]" value="<?= (int) $logro['id_logro'] ?>">

                                                <button type="button" class="btn-danger btn--icon js-remove-achievement-item" title="Eliminar logro">
                                                    <span class="material-symbols-outlined">delete</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <p class="empty-state__text">No hay logros agregados. ¡Comienza agregando uno!</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-primary btn-md">
                                <span class="material-symbols-outlined">save</span>
                                Guardar
                            </button>
                            <a href="?modo=editar&id=<?= (int) $juegoEdit['id_juego'] ?>" class="btn-secondary btn-md">
                                <span class="material-symbols-outlined">cancel</span>
                                Cancelar
                            </a>
                        </div>
                    </form>
                </section>
            <?php endif; ?>

            <?php if ($modo === 'listar'): ?>
                <section class="panel-card">
                    <div class="panel-card__header">
                        <strong>Tus juegos</strong>
                        <a href="#new" class="btn-primary btn-sm">
                            <span class="material-symbols-outlined icon-add-new">add</span>
                            Nuevo juego
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="panel-table">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Fecha de publicación</th>
                                    <th>Precio</th>
                                    <th>Descuento</th>
                                    <th style="width: 220px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($juegos as $juego): ?>
                                    <tr>
                                        <td><?= e($juego['nombre_juego']) ?></td>
                                        <td><?= e($juego['fecha_publicacion']) ?></td>
                                        <td><?= e((string) $juego['precio']) ?></td>
                                        <td><?= e((string) $juego['descuento']) ?></td>
                                        <td>
                                            <div class="table-actions">
                                                <a href="?modo=editar&id=<?= (int) $juego['id_juego'] ?>" class="btn-secondary btn-sm">
                                                    <span class="material-symbols-outlined icon-edit">edit</span>
                                                    Editar
                                                </a>
                                                <form method="post" class="form-inline" onsubmit="return confirm('¿Borrar este juego y todos sus archivos?');">
                                                    <input type="hidden" name="accion" value="eliminar_juego">
                                                    <input type="hidden" name="id_juego" value="<?= (int) $juego['id_juego'] ?>">
                                                    <button type="submit" class="btn-danger btn-sm del-game">
                                                        <span class="material-symbols-outlined icon-delete">delete</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                                <?php if (!$juegos): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">Todavía no has subido ningún juego.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            <?php endif; ?>
        </main>
    </div>
</div>

<div class="modal fade" id="imageCropModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content cropper-modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageCropModalLabel">Recortar imagen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body cropper-modal__body">
                <div class="cropper-modal__canvas">
                    <img id="imageCropTarget" alt="Recorte">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <span class="material-symbols-outlined">cancel</span>
                    <span>Cancelar</span>
                </button>
                <button type="button" class="btn btn-primary" id="applyCropBtn">
                    <span class="material-symbols-outlined">check</span>
                    <span>Usar recorte</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script src="../JS/settings-game.js"></script>
<?php require_once '../GENERAL/[main_END - footer].php'; ?>
<?php require_once '../GENERAL/[Page_END].php'; ?>