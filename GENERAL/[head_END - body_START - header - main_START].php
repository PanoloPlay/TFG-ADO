    <!-- Final de la sección de la cabeza del documento HTML -->
    </head>
    <!-- Inicio de la sección del cuerpo del documento HTML -->
    <body class="site-body">

        <?php require_once '../GENERAL/avatar_helpers.php'; ?>

        <!-- Encabezado del sitio web -->
        <header class="site-header">
            <div class="site-header__inner">
                <a class="site-brand" href="./">
                    <span class="site-brand__mark">
                        <img src="../IMG/app_icons/loto-color.svg" width="40" height="50" alt="Hestia's Lotus">
                    </span>
                    <span class="site-brand__text">
                        <strong>Hestia's Lotus</strong>
                        <small>TFG-ADO</small>
                    </span>
                </a>

                <nav class="site-nav">
                    <div class="dropdown">
                        <a class="chip dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="material-symbols-outlined">add_business</span>
                            Tienda
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark">
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="./">
                                    <span class="material-symbols-outlined">store</span>
                                    Página principal
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="../MAIN/new_releases.php">
                                    <span class="material-symbols-outlined">new_releases</span>
                                    Últimos lanzamientos
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="../MAIN/categories.php">
                                    <span class="material-symbols-outlined">shoppingmode</span>
                                    Categorías
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="../MAIN/offers.php">
                                    <span class="material-symbols-outlined">percent_discount</span>
                                    Ofertas
                                </a>
                            </li>
                        </ul>
                    </div>
                    <a href="../s">Soporte</a>
                </nav>

                <div class="site-actions">
                    <?php if (!empty($_SESSION['id_usuario'])): ?>
                        <?php
                            $headerNickname = $_SESSION['nickname'] ?? '';
                            $headerAvatarData = getProfileAvatarData($headerNickname);
                        ?>

                        <div class="dropdown">
                            <a class="chip dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <div
                                    class="header-avatar-fallback <?= e($headerAvatarData['avatarClass']) ?>"
                                    <?php if (!empty($headerAvatarData['avatarPath'])): ?>
                                        style="background-image: url('<?= e($headerAvatarData['avatarPath']) ?>');"
                                    <?php endif; ?>
                                >
                                    <?php if (empty($headerAvatarData['avatarPath'])): ?>
                                        <?= e($headerAvatarData['initial']) ?>
                                    <?php endif; ?>
                                </div>

                                <?= e($headerNickname) ?>
                            </a>

                            <ul class="dropdown-menu dropdown-menu-dark">
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2" href="../MAIN/profile.php">
                                        <span class="material-symbols-outlined">person</span>
                                        Mi Perfil
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2" href="../PHP/library.php">
                                        <span class="material-symbols-outlined">library_books</span>
                                        Biblioteca
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2" href="../MAIN/friends.php">
                                        <span class="material-symbols-outlined">group</span>
                                        Amigos
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2" href="../MAIN/wishlist.php">
                                        <span class="material-symbols-outlined">bookmark_add</span>
                                        Lista de deseados
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2" href="../MAIN/settings-profile.php">
                                        <span class="material-symbols-outlined">settings</span>
                                        Configuración
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2" href="../AUTH/logout.php">
                                        <span class="material-symbols-outlined">logout</span>
                                        Cerrar Sesión
                                    </a>
                                </li>
                            </ul>
                        </div>

                    <?php else: ?>
                        <a class="chip d-inline-flex align-items-center gap-2" href="../AUTH/login.php">
                            <span class="material-symbols-outlined">login</span>
                            Iniciar sesión
                        </a>
                        <a class="chip chip-soft d-inline-flex align-items-center gap-2" href="../AUTH/register.php">
                            <span class="material-symbols-outlined">person_add</span>
                            Registro
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <!-- Contenedor de la página -->
        <main class="site-main__shell">
            <div style="margin: 10px; height: calc(100% - 10px); overflow-y: auto; scrollbar-width: thin; scrollbar-color: transparent transparent;">