<!-- INICIO - PHP anterior a <html> -->

<?php 

?>

<!-- FIN - PHP anterior a <html> -->

<?php include_once '../GENERAL/S-html_S-head.php'; ?>

<!-- INICIO - Estilos personalizados para esta página y scripts -->

<style>
    
</style>

<!-- <link rel="stylesheet" href="../CSS/x.css"> -->

<!-- <script src="../JS/x.js" defer></script> -->

<!-- FIN - Estilos personalizados para esta página y scripts -->

<?php include_once '../GENERAL/header_E-head_S-body.php'; ?>

<!-- INICIO -Contenedor principal de la página -->

<div>
    <form action="../PHP/tienda.php" method="post" class="d-flex" role="search">
        <div>
            <h1>
                <i><u>Tienda</u></i>
            </h1>
        </div>
        <div style="margin-left: auto;">
            <input type="text" name="search" placeholder="Buscar juego...">
            <button type="submit">Buscar</button>
        </div>
    </form>
</div>
<hr>
<div class="border border-2 border-dark rounded-3 p-3" style="background-color: #474747; width:50%;">
    <u><h2>Partners</h2></u>
    <br>
    <div class="container-fluid">
        <div class="d-flex justify-content-start gap-3">
            <form action="../PHP/tiendaJuego.php" method="post" class="card" style="width: 25%;">
                <button>
                    <img src="../IMG/juegos/game.jpg" class="card-img-top" alt="...">
                    <div class="card-body">
                        <b>Scritchy Scratch</b>
                        [00.00$]
                    </div>
                </button>
            </form>
            <form action="../PHP/tiendaJuego.php" method="post" class="card" style="width: 25%;">
                <button>
                    <img src="../IMG/juegos/game.jpg" class="card-img-top" alt="...">
                    <div class="card-body">
                        <b>Scritchy Scratch</b>
                        [00.00$]
                    </div>
                </button>
            </form>
            <form action="../PHP/tiendaJuego.php" method="post" class="card" style="width: 25%;">
                <button>
                    <img src="../IMG/juegos/game.jpg" class="card-img-top" alt="...">
                    <div class="card-body">
                        <b>Scritchy Scratch</b>
                        [00.00$]
                    </div>
                </button>
            </form>
            <form action="../PHP/tiendaJuego.php" method="post" class="card" style="width: 25%;">
                <button>
                    <img src="../IMG/juegos/game.jpg" class="card-img-top" alt="...">
                    <div class="card-body">
                        <b>Scritchy Scratch</b>
                        [00.00$]
                    </div>
                </button>
            </form>
        </div>
        <br>
        <div class="d-flex justify-content-start gap-3">
            <form action="../PHP/tiendaJuego.php" method="post" class="card" style="width: 25%;">
                <button>
                    <img src="../IMG/juegos/game.jpg" class="card-img-top" alt="...">
                    <div class="card-body">
                        <b>Scritchy Scratch</b>
                        [00.00$]
                    </div>
                </button>
            </form>
            <form action="../PHP/tiendaJuego.php" method="post" class="card" style="width: 25%;">
                <button>
                    <img src="../IMG/juegos/game.jpg" class="card-img-top" alt="...">
                    <div class="card-body">
                        <b>Scritchy Scratch</b>
                        [00.00$]
                    </div>
                </button>
            </form>
            <form action="../PHP/tiendaJuego.php" method="post" class="card" style="width: 25%;">
                <button>
                    <img src="../IMG/juegos/game.jpg" class="card-img-top" alt="...">
                    <div class="card-body">
                        <b>Scritchy Scratch</b>
                        [00.00$]
                    </div>
                </button>
            </form>
            <form action="../PHP/tiendaJuego.php" method="post" class="card" style="width: 25%;">
                <button>
                    <img src="../IMG/juegos/game.jpg" class="card-img-top" alt="...">
                    <div class="card-body">
                        <b>Scritchy Scratch</b>
                        [00.00$]
                    </div>
                </button>
            </form>
        </div>
        <br>
        <div class="d-flex justify-content-center gap-3">
            <button id="firstPage-Partners" class="btn btn-dark"><<</button>
            <button id="prevPage-Partners" class="btn btn-dark"><</button>
            <button id="nextPage-Partners" class="btn btn-dark">></button>
            <button id="lastPage-Partners" class="btn btn-dark">>></button>
        </div>
    </div>
</div>

<!-- FIN -Contenedor principal de la página -->

<!-- INICIO - Fin Página -->
<?php include_once '../GENERAL/footer_E-body_E-html.php'; ?>
<!-- FIN - Fin Página -->