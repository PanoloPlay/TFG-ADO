

document.addEventListener('DOMContentLoaded', function () {
    // Seleccionar elementos del DOM
    const checkboxes = document.querySelectorAll('.categoria-checkbox');
    const gameCards = document.querySelectorAll('.game-card-library');
    const btnLimpiarFiltros = document.getElementById('btnLimpiarFiltros');
    const gameGrid = document.querySelector('.game-grid-library');
    const panelContent = document.querySelector('.panel');

    // Función para aplicar los filtros seleccionados y mostrar los juegos correspondientes
    function aplicarFiltros() {
        // Obtener las categorías seleccionadas
        const categoriasSeleccionadas = Array.from(checkboxes)
            // Filtra los checkboxes para obtener solo los que están marcados y luego mapear sus valores
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        // Si no hay categorías seleccionadas, mostrar todos los juegos, de lo contrario, filtrar por las categorías seleccionadas
        if (categoriasSeleccionadas.length === 0) {
            mostrarTodosJuegos();
        } else {
            filtrarPorCategorias(categoriasSeleccionadas);
        }
    }

    // Función para mostrar todos los juegos sin aplicar ningún filtro
    function mostrarTodosJuegos() {
        gameCards.forEach(card => {
            card.style.display = '';
        });
        verificarJuegosVisibles();
    }

    // Función para filtrar los juegos por las categorías seleccionadas utilizando una petición AJAX
    function filtrarPorCategorias(categorias) {
        const categoriasList = categorias.join(',');
        // Realizar una petición AJAX para obtener los juegos filtrados por las categorías seleccionadas
        $.ajax({
            url: '../AJAX/ajaxData.php',
            type: 'POST',
            dataType: 'json',
            data: {
                accion: 'filtrar_biblioteca_por_categorias',
                categorias: categoriasList
            },
            // En caso de éxito, mostrar solo los juegos que coincidan con las categorías seleccionadas
            success: function (response) {
                if (response.success && response.juegos) {
                    const juegosIds = response.juegos.map(j => j.id_juego);

                    gameCards.forEach(card => {
                        const juegoId = parseInt(card.getAttribute('data-juego-id'));
                        if (juegosIds.includes(juegoId)) {
                            card.style.display = '';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                    // Verificar si hay juegos visibles después de aplicar los filtros
                    verificarJuegosVisibles();
                } else {
                    // Si no se reciben juegos, mostrar el mensaje de "sin resultados"
                    mostrarSinResultados();
                }
            },
            error: function () {
                // En caso de error, mostrar todos los juegos
                mostrarTodosJuegos();
            }
        });
    }
    // Función para verificar si hay juegos visibles después de aplicar los filtros y mostrar el mensaje de "sin resultados" si no hay ninguno visible
    function verificarJuegosVisibles() {
        const juegosVisibles = Array.from(gameCards).some(card => card.style.display !== 'none');

        gameGrid.style.display = '';

        if (!juegosVisibles) {
            mostrarSinResultados();
        } else {
            const emptyState = panelContent.querySelector('.empty-state');
            if (emptyState) {
                emptyState.style.display = 'none';
            }
        }
    }
    // Función para mostrar el mensaje de "sin resultados" cuando no hay juegos que coincidan con los filtros seleccionados
    function mostrarSinResultados() {
        let emptyState = panelContent.querySelector('.empty-state');

        if (!emptyState) {
            emptyState = document.createElement('div');
            emptyState.className = 'empty-state';
            emptyState.innerHTML = `
                <span class="material-symbols-outlined">search_off</span>
                <p>No hay juegos que coincidan con los filtros seleccionados.</p>
            `;
            panelContent.appendChild(emptyState);
        } else {
            emptyState.style.display = '';
        }

        gameGrid.style.display = 'none';
    }

    function mostrarGridJuegos() {
        gameGrid.style.display = '';
        const emptyState = panelContent.querySelector('.empty-state');
        if (emptyState) {
            emptyState.style.display = 'none';
        }
    }

    function limpiarFiltros() {
        checkboxes.forEach(cb => cb.checked = false);
        mostrarTodosJuegos();
        mostrarGridJuegos();
    }

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', aplicarFiltros);
    });

    btnLimpiarFiltros.addEventListener('click', limpiarFiltros);

    mostrarGridJuegos();
});
