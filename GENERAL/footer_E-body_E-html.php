            </div>
        </main>

        <!-- Encabezado de la página -->
        <header>
            <!-- Contenido del encabezado de la página -->
            <div id="header-content">
                <div class="container d-flex align-items-center justify-content-center">
                    <div class="row">
                        <div class="col-11">
                            <div class="title">
                                <h3 class="text-center">Trabajo Final de Grado - Óscar, Ahmad, David</h3>
                            </div>
                        </div>
                        <div class="col-1">
                            <div class="logo">
                                <img src="../IMG/app_icons/icon.png" alt="Logo de la página">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Barra de navegación de la página -->
            <nav class="navbar navbar-expand-lg bg-body-tertiary">

                <div class="container-fluid">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="container-fluid collapse navbar-collapse" id="navbarNav" style="width: 50%;">

                        <div class="container-fluid btn-group">
                            <button type="button" class="btn btn-secondary nav-item">
                                <a class="nav-link active" aria-current="page" href="../PHP/inicio.php">Inicio</a>
                            </button>
                        </div>

                        <div class="container-fluid btn-group">
                            <button type="button" class="btn btn-secondary" style="width:80%;">
                                <a class="nav-link active" aria-current="page" href="../PHP/tienda.php">Tienda</a>
                            </button>
                            <button type="button" class="btn btn-dark dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="visually-hidden">Toggle Dropdown</span>
                            </button>
                            <ul class="container-fluid dropdown-menu dropdown-menu-dark">
                                <li><a class="dropdown-item" href="#">Action</a></li>
                                <li><a class="dropdown-item" href="#">Another action</a></li>
                                <li><a class="dropdown-item" href="#">Something else here</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#">Separated link</a></li>
                            </ul>
                        </div>
                        
                        <div class="container-fluid btn-group">
                            <button type="button" class="btn btn-secondary" style="width:80%;">
                                <a class="nav-link active" aria-current="page" href="../PHP/biblioteca.php">Biblioteca</a>
                            </button>
                            <button type="button" class="btn btn-dark dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="visually-hidden">Toggle Dropdown</span>
                            </button>
                            <ul class="container-fluid dropdown-menu dropdown-menu-dark">
                                <li><a class="dropdown-item" href="#">Action</a></li>
                                <li><a class="dropdown-item" href="#">Another action</a></li>
                                <li><a class="dropdown-item" href="#">Something else here</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#">Separated link</a></li>
                            </ul>
                        </div>

                        <div class="container-fluid btn-group">
                            <button type="button" class="btn btn-secondary" style="width:80%;">
                                <a class="nav-link active" aria-current="page" href="../PHP/amigos.php">Amigos</a>
                            </button>
                            <button type="button" class="btn btn-dark dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="visually-hidden">Toggle Dropdown</span>
                            </button>
                            <ul class="container-fluid dropdown-menu dropdown-menu-dark">
                                <li><a class="dropdown-item" href="#">Action</a></li>
                                <li><a class="dropdown-item" href="#">Another action</a></li>
                                <li><a class="dropdown-item" href="#">Something else here</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#">Separated link</a></li>
                            </ul>
                        </div>

                    <!-- Perfil Usuario -->
                    <?php
                                    
                        if (isset($_SESSION['username'])) {
                            ?>
                                <div class="container-fluid collapse navbar-collapse" id="navbarNav">
                                    <div class="container-fluid btn-group">
                                        <button type="button" class="btn btn-secondary" style="width:80%;">
                                            <a class="nav-link active" aria-current="page" href="../PHP/amigos.php">
                                                <?php echo $_SESSION['username']; ?>
                                            </a>
                                        </button>
                                        <button type="button" class="btn btn-dark dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                            <span class="visually-hidden">Toggle Dropdown</span>
                                        </button>
                                        <ul class="container-fluid dropdown-menu dropdown-menu-dark">
                                            <li><a class="dropdown-item" href="#">Action</a></li>
                                            <li><a class="dropdown-item" href="#">Another action</a></li>
                                            <li><a class="dropdown-item" href="#">Something else here</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="#">Separated link</a></li>
                                        </ul>
                                    </div>
                                </div>
                            <?php
                        } 
                        else {
                            ?>
                                <div class="container-fluid btn-group">
                                    <button type="button" class="btn btn-dark nav-item">
                                        <a class="nav-link active" aria-current="page" href="../AUTH/login.php">
                                            Iniciar Sesión
                                        </a>
                                    </button>
                                </div>
                            <?php
                        }
                    ?>     
                    </div>
                </div>
            </nav>
        </header>

        <!-- Pie de página de la página -->
        <footer>

        </footer>
    </body>

</html>