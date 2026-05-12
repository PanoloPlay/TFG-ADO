let userNickname = "";
let gameName = "";
let gameId = 0;
let userLangPrimary = "";
let userLangSecondary = "";

$(window).on("load", function () {
    userNickname = String($("#hdnSession").data("value") || "");
    gameId = Number($("#hdnGameId").data("value") || 0);
    gameName = String($("#hdnGameName").data("value") || "");
    userLangPrimary = String($("#hdnUserLangPrimary").data("value") || "");
    userLangSecondary = String($("#hdnUserLangSecondary").data("value") || "");

    initInteractions();
    initReviewFilters();
    initCommentActions();
    initDateDropdown();
});

function initInteractions() {
    $("#buy-button").on("click", function () {
        buyGame();
    });

    $("#add-wishlist-button").on("click", function () {
        addWishlist();
    });

    $("#remove-wishlist-button").on("click", function () {
        removeWishlist();
    });
}

function parseDateInput(value, endOfDay = false) {
    if (!value) return null;

    const parts = String(value).split("-");
    if (parts.length !== 3) return null;

    const year = Number(parts[0]);
    const month = Number(parts[1]);
    const day = Number(parts[2]);

    if (!year || !month || !day) return null;

    return endOfDay
        ? new Date(year, month - 1, day, 23, 59, 59, 999)
        : new Date(year, month - 1, day, 0, 0, 0, 0);
}

function getFilterValues() {
    return {
        search: String($("#review-search").val() || "").trim().toLowerCase(),
        rating: String($("#ratingDropdown").data("current-value") || "all"),
        languageFilter: String($("#languageDropdown").data("current-value") || "all"),
        sortMode: String($("#sortDropdown").data("current-value") || "new"),
        dateFrom: parseDateInput($("#review-date-from").val(), false),
        dateTo: parseDateInput($("#review-date-to").val(), true)
    };
}

function initReviewFilters() {
    $("#review-search, #review-date-from, #review-date-to").on("input change", function () {
        filterAndSortReviews();
    });

    $(document).on("click", ".dropdown-item[data-value]", function (e) {
        e.preventDefault();

        const $this = $(this);
        const value = String($this.data("value") || "all");
        const icon = String($this.data("icon") || "");
        const text = $this.clone().find(".material-symbols-outlined").remove().end().text().trim();
        const buttonId = $this.closest(".dropdown-menu").attr("aria-labelledby");

        if (buttonId === "ratingDropdown") {
            $("#ratingDropdown").data("current-value", value);
            $("#selected-rating-icon").text(icon || "star");
            $("#selected-rating-text").text(text);
        } else if (buttonId === "languageDropdown") {
            $("#languageDropdown").data("current-value", value);
            $("#selected-language-icon").text(icon || "language");
            $("#selected-language-text").text(text);
        } else if (buttonId === "sortDropdown") {
            $("#sortDropdown").data("current-value", value);
            $("#selected-sort-icon").text(icon || "sort");
            $("#selected-sort-text").text(text);
        }

        filterAndSortReviews();
    });

    $("#review-reset").on("click", function () {
        $("#review-search").val("");
        $("#review-date-from").val("");
        $("#review-date-to").val("");

        $("#ratingDropdown").data("current-value", "all");
        $("#selected-rating-icon").text("star");
        $("#selected-rating-text").text("Todas las valoraciones");

        $("#languageDropdown").data("current-value", "all");
        $("#selected-language-icon").text("language");
        $("#selected-language-text").text("Todos los idiomas");

        $("#sortDropdown").data("current-value", "new");
        $("#selected-sort-icon").text("sort");
        $("#selected-sort-text").text("Más recientes");

        filterAndSortReviews();
    });

    $("#ratingDropdown").data("current-value", "all");
    $("#languageDropdown").data("current-value", "all");
    $("#sortDropdown").data("current-value", "new");

    filterAndSortReviews();
}

function filterAndSortReviews() {
    const { search, rating, languageFilter, sortMode, dateFrom, dateTo } = getFilterValues();

    const comments = Array.from(document.querySelectorAll("#comment-list .comment-card"));
    const noResults = document.getElementById("no-comment-results");

    if (comments.length === 0) {
        if (noResults) noResults.style.display = "block";
        return;
    }

    let filtered = comments.filter((card) => {
        const searchText = String(card.dataset.searchText || "").toLowerCase();
        const commentRating = String(card.dataset.valoracion || "");
        const commentLanguage = String(card.dataset.idioma || "");
        const commentTimestamp = Number(card.dataset.timestamp || 0);

        const matchesSearch = !search || searchText.includes(search);
        const matchesRating = rating === "all" || commentRating === rating;
        const matchesLanguage =
            languageFilter === "all" ||
            (languageFilter === "my" && isMyLanguage(commentLanguage));

        let matchesDate = true;
        if (dateFrom || dateTo) {
            const commentDate = new Date(commentTimestamp * 1000);

            if (dateFrom && commentDate < dateFrom) {
                matchesDate = false;
            }

            if (dateTo && commentDate > dateTo) {
                matchesDate = false;
            }
        }

        return matchesSearch && matchesRating && matchesLanguage && matchesDate;
    });

    filtered.sort((a, b) => {
        const dateA = Number(a.dataset.timestamp || 0);
        const dateB = Number(b.dataset.timestamp || 0);

        if (sortMode === "old") {
            return dateA - dateB;
        }

        if (sortMode === "positive") {
            const aPositive = a.dataset.valoracion === "positiva" ? 1 : 0;
            const bPositive = b.dataset.valoracion === "positiva" ? 1 : 0;

            if (bPositive !== aPositive) {
                return bPositive - aPositive;
            }

            return dateB - dateA;
        }

        if (sortMode === "negative") {
            const aNegative = a.dataset.valoracion === "negativa" ? 1 : 0;
            const bNegative = b.dataset.valoracion === "negativa" ? 1 : 0;

            if (bNegative !== aNegative) {
                return bNegative - aNegative;
            }

            return dateB - dateA;
        }

        return dateB - dateA;
    });

    comments.forEach((card) => {
        card.style.display = "none";
    });

    filtered.forEach((card) => {
        card.style.display = "";
        document.getElementById("comment-list").appendChild(card);
    });

    if (noResults) {
        noResults.style.display = filtered.length === 0 ? "block" : "none";
    }
}

function isMyLanguage(languageCode) {
    if (!languageCode) {
        return false;
    }

    return (
        languageCode === userLangPrimary ||
        languageCode === userLangSecondary
    );
}

function initCommentActions() {
    $("#submit-comment-button").on("click", createComment);
    $("#edit-comment-button").on("click", toggleCommentEdit);
    $("#save-comment-button").on("click", saveComment);
    $("#delete-comment-button").on("click", deleteComment);
}

function getSelectedRating(formSelector) {
    const selected = document.querySelector(`${formSelector} input[type="radio"]:checked`);
    return selected ? selected.value : "";
}

function toggleCommentEdit() {
    const $editButton = $("#edit-comment-button");
    const isEditing = $editButton.data("editing") === true;

    if (!isEditing) {
        const currentComment = $("#comment-input").val();
        const currentRating = getSelectedRating("#comment-form-edit");

        $editButton.data("editing", true);
        $editButton.data("original-comment", currentComment);
        $editButton.data("original-rating", currentRating);
        $editButton.text("Cancelar");

        $("#comment-input").prop("disabled", false);
        $("#review-vote-group-edit input[type='radio']").prop("disabled", false);
        $("#review-vote-group-edit").removeClass("is-disabled");

        $("#save-comment-button")
            .removeClass("is-hidden")
            .prop("disabled", false);

        $("#delete-comment-button")
            .removeClass("is-hidden")
            .prop("disabled", false);
    } else {
        const originalComment = $editButton.data("original-comment") || "";
        const originalRating = $editButton.data("original-rating") || "";

        $("#comment-input").val(originalComment).prop("disabled", true);
        $("#review-vote-group-edit input[type='radio']").prop("disabled", true);
        $("#review-vote-group-edit").addClass("is-disabled");

        if (originalRating) {
            $(`#review-vote-group-edit input[type='radio'][value='${originalRating}']`).prop("checked", true);
        }

        $("#save-comment-button")
            .addClass("is-hidden")
            .prop("disabled", true);

        $("#delete-comment-button")
            .addClass("is-hidden")
            .prop("disabled", true);

        $editButton.data("editing", false);
        $editButton.text("Editar");
    }
}

function initDateDropdown() {
    const toggleButton = $("#review-date-toggle");
    const dropdown = $("#date-dropdown");

    toggleButton.on("click", function () {
        dropdown.toggleClass("active");
        toggleButton.toggleClass("active");
    });

    $(document).on("click", function (e) {
        if (!$(e.target).closest(".date-range-wrapper").length) {
            dropdown.removeClass("active");
            toggleButton.removeClass("active");
        }
    });
}

function redirectWithGameMessage(message, type = "success") {
    const url = new URL(window.location.href);
    url.searchParams.set("game_message", message);
    url.searchParams.set("game_message_type", type);
    window.location.href = url.toString();
}

async function createComment() {
    if (!userNickname) {
        showGameAlert("Debes iniciar sesión.", "warning");
        return;
    }

    const comment = String($("#comment-input-new").val() || "").trim();
    const rating = getSelectedRating("#comment-form-new");

    if (!comment) {
        showGameAlert("Escribe un comentario antes de enviarlo.", "warning");
        return;
    }

    if (!rating) {
        showGameAlert("Selecciona una valoración.", "warning");
        return;
    }

    try {
        const result = await checkField_4(
            gameName,
            userNickname,
            rating,
            comment,
            "create_comment"
        );

        if (result && result.success) {
            redirectWithGameMessage(result.message || "Comentario creado correctamente.", "success");
        } else {
            showGameAlert(result?.message || "No se pudo crear el comentario.", "danger");
        }
    } catch (error) {
        console.error(error);
        showGameAlert("Ha ocurrido un error al crear el comentario.", "danger");
    }
}

async function saveComment() {
    if (!userNickname) {
        showGameAlert("Debes iniciar sesión.", "warning");
        return;
    }

    const comment = String($("#comment-input").val() || "").trim();
    const rating = getSelectedRating("#comment-form-edit");

    if (!comment) {
        showGameAlert("Escribe un comentario antes de guardar.", "warning");
        return;
    }

    if (!rating) {
        showGameAlert("Selecciona una valoración.", "warning");
        return;
    }

    try {
        const result = await checkField_4(
            gameName,
            userNickname,
            rating,
            comment,
            "update_comment"
        );

        if (result && result.success) {
            redirectWithGameMessage(result.message || "Comentario actualizado correctamente.", "success");
        } else {
            showGameAlert(result?.message || "No se pudo actualizar el comentario.", "danger");
        }
    } catch (error) {
        console.error(error);
        showGameAlert("Ha ocurrido un error al actualizar el comentario.", "danger");
    }
}

async function deleteComment() {
    if (!userNickname) {
        showGameAlert("Debes iniciar sesión.", "warning");
        return;
    }

    if (!confirm("¿Seguro que quieres borrar tu reseña?")) {
        return;
    }

    try {
        const result = await checkField_2(
            gameName,
            userNickname,
            "delete_comment"
        );

        if (result && result.success) {
            redirectWithGameMessage(result.message || "Comentario eliminado correctamente.", "success");
        } else {
            showGameAlert(result?.message || "No se pudo eliminar el comentario.", "danger");
        }
    } catch (error) {
        console.error(error);
        showGameAlert("Ha ocurrido un error al eliminar el comentario.", "danger");
    }
}

async function buyGame() {
    if (!userNickname) {
        redirectWithGameMessage("Debes iniciar sesión para comprar juegos.", "danger");
        return;
    }

    try {
        const result = await checkField_2(
            gameName,
            userNickname,
            "buy_game"
        );

        if (result && result.success) {
            redirectWithGameMessage("Juego añadido a la biblioteca.", "success");
        } else {
            redirectWithGameMessage("Ha ocurrido un error al comprar.", "danger");
        }
    } catch (error) {
        console.error(error);
        redirectWithGameMessage("Ha ocurrido un error al comprar.", "danger");
    }
}

async function addWishlist() {
    if (!userNickname) {
        redirectWithGameMessage("Debes iniciar sesión.", "danger");
        return;
    }

    try {
        const result = await checkField_2(
            gameName,
            userNickname,
            "add_wishlist"
        );

        if (result && result.success) {
            redirectWithGameMessage("Juego añadido a la wishlist.", "success");
        } else {
            redirectWithGameMessage("No se pudo añadir a wishlist.", "danger");
        }
    } catch (error) {
        console.error(error);
        redirectWithGameMessage("Ha ocurrido un error al añadir a wishlist.", "danger");
    }
}

async function removeWishlist() {
    if (!userNickname) {
        redirectWithGameMessage("Debes iniciar sesión.", "danger");
        return;
    }

    try {
        const result = await checkField_2(
            gameName,
            userNickname,
            "remove_wishlist"
        );

        if (result && result.success) {
            redirectWithGameMessage("Juego eliminado de wishlist.", "success");
        } else {
            redirectWithGameMessage("No se pudo eliminar de wishlist.", "danger");
        }
    } catch (error) {
        console.error(error);
        redirectWithGameMessage("Ha ocurrido un error al eliminar de wishlist.", "danger");
    }
}

function pauseVideoIfPlaying() {
    const videos = document.querySelectorAll(".video-carousel");

    videos.forEach((video) => {
        if (!video.paused) {
            video.pause();
        }
    });
}