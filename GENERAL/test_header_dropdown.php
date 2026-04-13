    <!-- Final de la sección de la cabeza del documento HTML -->
    </head>
    <!-- Inicio de la sección del cuerpo del documento HTML -->
    <body class="site-body">
        <!-- Encabezado del sitio web -->
        <header class="site-header">
            <div class="site-header__inner">
                <a class="site-brand" href="./">
                    <span class="site-brand__mark">TFG</span>
                    <span class="site-brand__text">
                        <strong>Store</strong>
                        <small>Videojuegos</small>
                    </span>
                </a>

                <nav class="site-nav">
                    <a href="../">Tienda</a>
                    <a href="../news.php">Noticias</a>
                    <a href="../support.php">Soporte</a>
                </nav>

                <div class="site-actions">
                    <?php if (!empty($_SESSION['id_usuario'])): ?>

                        <div class="dropdown">
                            <a class="chip dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?= e($_SESSION['nickname']) ?>
                            </a>

                            <ul class="dropdown-menu dropdown-menu-dark">
                                <li><a class="dropdown-item" href="../MAIN/profile.php">Mi Perfil</a></li>
                                <li><a class="dropdown-item" href="../PHP/library.php">Biblioteca</a></li>
                                <li><a class="dropdown-item" href="../MAIN/friends.php">Amigos</a></li>
                                <li><a class="dropdown-item" href="../MAIN/settings.php">Configuración</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="../AUTH/logout.php">Cerrar Sesión</a></li>
                            </ul>
                        </div>

                    <?php else: ?>
                        <a class="chip" href="../AUTH/login.php">Entrar</a>
                        <a class="chip chip--soft" href="../AUTH/register.php">Registro</a>
                    <?php endif; ?>
                </div>
            </div>
        </header>
        <!-- Contenedor de la página -->
        <main class="site-main__shell">
            <div style="margin: 10px; height: calc(100% - 10px); overflow-y: auto; scrollbar-width: thin; scrollbar-color: transparent transparent;">