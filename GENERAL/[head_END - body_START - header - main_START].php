    <!-- Final de la sección de la cabeza del documento HTML -->
    </head>
    <!-- Inicio de la sección del cuerpo del documento HTML -->
    <body class="site-body">

        <!-- Encabezado del sitio web -->
        <header class="site-header">
            <div class="site-header__inner">
                <a class="site-brand" href="./">
                    <span class="site-brand__mark">
                        <img src="../MEDIA/IMG/app_icons/loto-color.svg" width="40" height="50" alt="Hestia's Lotus">
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
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="./">
                                    <span class="material-symbols-outlined">store</span>
                                    <span>Página principal</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="../MAIN/shop-new.php">
                                    <span class="material-symbols-outlined">new_releases</span>
                                    <span>Últimos lanzamientos</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="../MAIN/shop-category.php">
                                    <span class="material-symbols-outlined">shoppingmode</span>
                                    <span>Categorías</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="../MAIN/shop-offers.php">
                                    <span class="material-symbols-outlined">percent_discount</span>
                                    <span>Ofertas</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <a href="../MAIN/support.php">Soporte</a>
                </nav>

                <div class="site-actions">
                    <?php if (!empty($_SESSION['id_usuario'])): ?>
                        <?php
                            $headerNickname = $_SESSION['nickname'] ?? '';
                            $headerAvatarData = getProfileAvatarData($headerNickname);
                        ?>

                        <a class="chip d-inline-flex align-items-center gap-2" id="cart-button" href="../MAIN/cart.php" type="button">
                            <span class="material-symbols-outlined">shopping_cart</span>
                            <span id="cart-count">0</span>
                        </a>

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

                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2" href="../MAIN/profile.php">
                                        <span class="material-symbols-outlined">person</span>
                                        <span>Mi Perfil</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2" href="../MAIN/Library.php">
                                        <span class="material-symbols-outlined">library_books</span>
                                        <span>Biblioteca</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2" href="../MAIN/friends.php">
                                        <span class="material-symbols-outlined">group</span>
                                        <span>Amigos</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2" href="../MAIN/wishlist.php">
                                        <span class="material-symbols-outlined">bookmark_add</span>
                                        <span>Lista de deseados</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2" href="../MAIN/settings-profile.php">
                                        <span class="material-symbols-outlined">settings</span>
                                        <span>Configuración</span>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2" href="../AUTH/logout.php">
                                        <span class="material-symbols-outlined">logout</span>
                                        <span>Cerrar Sesión</span>
                                    </a>
                                </li>
                            </ul>
                        </div>

                    <?php else: ?>
                        <a class="chip d-inline-flex align-items-center gap-2" href="../AUTH/login.php">
                            <span class="material-symbols-outlined">login</span>
                            <span>Iniciar sesión</span>
                        </a>
                        <a class="chip chip-soft d-inline-flex align-items-center gap-2" href="../AUTH/register.php">
                            <span class="material-symbols-outlined">person_add</span>
                            <span>Registro</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <!-- Contenedor de la página -->
        <main class="site-main__shell">
            <div style="margin: 10px; height: calc(100% - 10px); overflow-y: auto; scrollbar-width: thin; scrollbar-color: transparent transparent;">