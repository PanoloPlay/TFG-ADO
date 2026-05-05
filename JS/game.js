let params = new URLSearchParams(document.location.search);
var userNickname;

var gameName;

var like = true;

var userLanguages;

var game;
var bought;
var userReview;
var comments;
var positive;
var wishlist;
var categories;

var commentsSorted;
var isFiltered = 0;

var commentFilterMode = 0;

$(window).on("load", async function() {
    if (await getAllData()) {
        await setUp();
    }
});

async function orderByDateAsc() {
    await (commentsSorted = await commentsSorted.sort((a, b) => a.fechaPublicacion.localeCompare(b.fechaPublicacion)));
    await setUpComments();
}

async function orderByDateDesc() {
    await (commentsSorted = await commentsSorted.sort((a, b) => b.fechaPublicacion.localeCompare(a.fechaPublicacion)));
    await setUpComments();
}


async function getAllData() {
    userNickname = $("#hdnSession").data('value');
    gameName = params.get("name");

    if (gameName != null) {

        gameName = gameName.replaceAll("_", " ");
    }
    else {

        let errorParam =  document.createElement("div");
        errorParam.className = "error-message";

        let errorTitle = document.createElement("h2");
        errorTitle.textContent = "Juego no encontrado";

        let errorText = document.createElement("p");
        errorText.textContent = "Lo sentimos, el juego que buscas no existe.";

        let errorButton = document.createElement("a");
        errorButton.className = "btn";
        errorButton.textContent = "Volver al inicio";
        errorButton.href = "./";

        errorParam.appendChild(errorTitle);
        errorParam.appendChild(errorText);
        errorParam.appendChild(errorButton);

        document.getElementById("error-section").appendChild(errorParam);

        return false;
    }

    game = await checkField_1(gameName, "get_game", "../AJAX/gameData.php");
    if (!game) {
        
        window.location.href = './game.php?error=GameNotFound';
        return false;
    }

    if (userNickname != null) {

        bought = await checkField_2(gameName, userNickname, "check_bought", "../AJAX/gameData.php");

        if (bought != null) {
            userReview = await checkField_2(gameName, userNickname, "user_comment", "../AJAX/gameData.php");
        }
    }

    comments = await checkField_1(gameName, "get_comments", "../AJAX/gameData.php");

    commentsSorted = await checkField_1(gameName, "get_comments", "../AJAX/gameData.php");

    positive = await checkField_1(gameName, "positive", "../AJAX/gameData.php");

    wishlist = await checkField_2(gameName, userNickname, "get_wishlist", "../AJAX/gameData.php");

    userLanguages = await checkField_2(gameName, userNickname, "get_languages", "../AJAX/gameData.php");

    categories = await checkField_1(gameName, "get_game_categories", "../AJAX/gameData.php");

    return true;
}

async function setUp() {

    document.getElementById("big-game-title").textContent = game[0]['nombre_juego'];
    document.getElementById("game-description").textContent = game[0]['descripcion'];
    document.getElementById("game-developer").textContent = "Desarrollador: " + game[0]['desarrollador'];
    let date = new Date(game[0]['fecha_publicacion']);
    document.getElementById("game-release-date").textContent = "Fecha de publicación: " + date.toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' });

    setUpGameCategories();

    setUpGameInfo();
    setUpPurchaseSection();
    setUpComments();
}

async function setUpGameCategories() {

    let categorySection = document.getElementById("game-categories");
    categorySection.innerHTML = "";

    for (let pos = 0; pos < categories.length; pos++) {

        let categoryButton = document.createElement("button");
        categoryButton.className = "category-button"
        categoryButton.textContent = categories[pos]["categoria"];

        categoryButton.addEventListener("click", goToShopFilterByCategory);
        
        categorySection.appendChild(categoryButton);
    }
}

async function setUpGameInfo() {
    
    if (comments == null) {

        document.getElementById("game-rating").textContent = "Aún no hay valoraciones para este juego.";
        document.getElementById("game-rating").className = "game-rating-none";
    } 
    else {

        if (positive == null) {

            document.getElementById("game-rating").textContent = "Valoraciones muy negativas (" + comments.length + ")";
            document.getElementById("game-rating").className = "game-rating-terrible";
        }
        else {

            let porcentage = (positive.length / comments.length) * 100;
            

            if (porcentage > 80) {

                document.getElementById("game-rating").textContent = "Valoraciones muy positivas (" + comments.length + ")";
                document.getElementById("game-rating").className = "game-rating-excellent";
            }
            else if (porcentage > 60) {

                document.getElementById("game-rating").textContent = "Valoraciones positivas (" + comments.length + ")";
                document.getElementById("game-rating").className = "game-rating-good";
            }
            else if (porcentage > 40) {

                document.getElementById("game-rating").textContent = "Valoraciones variadas (" + comments.length + ")";
                document.getElementById("game-rating").className = "game-rating-neutral";
            }
            else if (porcentage > 20) {

                document.getElementById("game-rating").textContent = "Valoraciones negativas (" + comments.length + ")";
                document.getElementById("game-rating").className = "game-rating-bad";
            }
            else {

                document.getElementById("game-rating").textContent = "Valoraciones muy negativas (" + comments.length + ")";
                document.getElementById("game-rating").className = "game-rating-terrible";
            }
        }
    }
}

async function setUpPurchaseSection() {

    let purchaseSection = document.getElementById("purchase-section");
    purchaseSection.innerHTML = "";

    if (bought != null) {

        let owned = document.createElement("div");
        owned.className = "owned-message";
        owned.textContent = "¡Ya has comprado este juego!";

        purchaseSection.appendChild(owned);

        let title = document.createElement("div");
        title.className = "game-title";
        title.textContent = "Descargar: " + game[0]['nombre_juego'];

        purchaseSection.appendChild(title);

        let downloadButton = document.createElement("button");
        downloadButton.className = "download-button";
        downloadButton.textContent = "Descargar";

        downloadButton.addEventListener("click", function() {
            window.location.href = './libraryGame.php?name=' + gameName.replaceAll(" ", "_");
        });

        purchaseSection.appendChild(downloadButton);
    }
    else {

        let title = document.createElement("div");
        title.className = "game-title";
        title.textContent = "Comprar: " + game[0]['nombre_juego'];

        purchaseSection.appendChild(title);

        let price = document.createElement("div");
        price.className = "game-price";

        if (game[0]['descuento'] == 0 || game[0]['descuento'] == null) {
            
            price.textContent = game[0]['precio'] + "€";
        }
        else {

            let discount = document.createElement("div");
            discount.className = "game-discount";
            discount.textContent = game[0]['descuento'] + "%";

            let discountedPrice = (game[0]['precio'] - (game[0]['precio'] * (game[0]['descuento'] / 100)));

            price.className = "game-price";
            price.textContent = discountedPrice.toFixed(2) + "€";

            purchaseSection.appendChild(discount);
        }
        
        purchaseSection.appendChild(price);

        let buyButton = document.createElement("button");
        buyButton.className = "buy-button";
        buyButton.textContent = "Comprar";

        buyButton.addEventListener("click", buyGame);

        purchaseSection.appendChild(buyButton);

        if (!wishlist) {

            let addWishlistButton = document.createElement("button");
            addWishlistButton.className = "wishlist-button";
            addWishlistButton.textContent = "Añadir a la lista de deseos";

            addWishlistButton.addEventListener("click", addToWishlist);

            purchaseSection.appendChild(addWishlistButton);
        }
        else {

            let removeWishlistButton = document.createElement("button");
            removeWishlistButton.className = "wishlist-button";
            removeWishlistButton.textContent = "Quitar de la lista de deseos";

            removeWishlistButton.addEventListener("click", removeToWishlist);

            purchaseSection.appendChild(removeWishlistButton);
        }
    }

}

async function setUpComments() {

    let commentsSection = document.getElementById("comment-section");
    commentsSection.innerHTML = "";

    if (bought != null) {

        let userComments = document.createElement("div");
        
        if (userReview == null) {

            userComments.className = "owned-make-comment";

            let newP = document.createElement("p");
            newP.textContent = "Escribe tu opinión sobre " + game[0]['nombre_juego'] + " aquí:";

            userComments.appendChild(newP);

            let commentInput = document.createElement("textarea");
            commentInput.id = "comment-input-new";
            commentInput.placeholder = "Escribe tu comentario aquí...";

            userComments.appendChild(commentInput);

            let likeButton = document.createElement("button");
            likeButton.id = "like-button";

            let likeSpan = document.createElement("span");
            likeSpan.id = "likeDislike";
            likeSpan.className = "material-symbols-outlined";

            if (like) {
                likeButton.className = "like";
                likeSpan.textContent = "thumb_up";
            }
            else {
                likeButton.className = "dislike";
                likeSpan.textContent = "thumb_down";
            }

            likeButton.appendChild(likeSpan);

            userComments.appendChild(likeButton);

            likeButton.addEventListener("click", likeDislike);

            let submitButton = document.createElement("button");
            submitButton.id = "submit-comment-button";
            submitButton.textContent = "Enviar comentario";

            submitButton.addEventListener("click", submitComment);

            userComments.appendChild(submitButton);
        }
        else {

            userComments.className = "owned-edit-comment";

            let newP = document.createElement("p");
            newP.textContent = "Tu opinión sobre " + game[0]['nombre_juego'] + " esta aquí:";

            userComments.appendChild(newP);

            let commentInput = document.createElement("textarea");
            commentInput.id = "comment-input";
            commentInput.placeholder = "Escribe tu comentario aquí...";
            commentInput.value = userReview[0]['comentario'];
            commentInput.disabled = true;

            userComments.appendChild(commentInput);

            let likeButton = document.createElement("button");
            likeButton.id = "like-button-new";

            let likeSpan = document.createElement("span");
            likeSpan.id = "likeDislike";
            likeSpan.className = "material-symbols-outlined";

            if (like) {
                likeButton.className = "like";
                likeSpan.textContent = "thumb_up";
            }
            else {
                likeButton.className = "dislike";
                likeSpan.textContent = "thumb_down";
            }

            likeButton.disabled = true;

            likeButton.appendChild(likeSpan);

            userComments.appendChild(likeButton);

            likeButton.addEventListener("click", likeDislike);

            let editButton = document.createElement("button");
            editButton.id = "edit-comment-button";
            editButton.textContent = "Editar";

            editButton.addEventListener("click", editComment);

            userComments.appendChild(editButton);

            let saveButton = document.createElement("button");
            saveButton.id = "save-comment-button";
            saveButton.textContent = "Guardar cambios";
            saveButton.disabled = true;

            saveButton.addEventListener("click", saveComment);

            userComments.appendChild(saveButton);

            let deleteButton = document.createElement("button");
            deleteButton.id = "delete-comment-button";
            deleteButton.textContent = "Borrar comentario";
            deleteButton.disabled = true;

            deleteButton.addEventListener("click", deleteComment);

            userComments.appendChild(deleteButton);
        }

        commentsSection.appendChild(userComments);

    }

    if (commentsSorted == null) {

        let noComments = document.createElement("div");
        noComments.className = "no-comments";
        noComments.textContent = "Aún no hay comentarios para este juego.";

        commentsSection.appendChild(noComments);
    }
    else {
        if (userNickname != null && userNickname != "") {

            let filterButtonAll = document.createElement("button");
            filterButtonAll.id = 0;
            filterButtonAll.className = "filter-button";
            filterButtonAll.textContent = "Filtro: Todos los comentarios";
            filterButtonAll.addEventListener("click", filterComments);

            commentsSection.appendChild(filterButtonAll);

            let filterButtonLanguage = document.createElement("button");
            filterButtonLanguage.id = 1;
            filterButtonLanguage.className = "filter-button";
            filterButtonLanguage.textContent = "Filtro: Comentarios de tu idioma";
            filterButtonLanguage.addEventListener("click", filterComments);

            commentsSection.appendChild(filterButtonLanguage);

            let filterButtonPositive = document.createElement("button");
            filterButtonPositive.id = 2;
            filterButtonPositive.className = "filter-button";
            filterButtonPositive.textContent = "Filtro: Comentarios positivos de tu idioma";
            filterButtonPositive.addEventListener("click", filterComments);

            commentsSection.appendChild(filterButtonPositive);

            let filterButtonNegative = document.createElement("button");
            filterButtonNegative.id = 3;
            filterButtonNegative.className = "filter-button";
            filterButtonNegative.textContent = "Filtro: Comentarios negativos de tu idioma";
            filterButtonNegative.addEventListener("click", filterComments);

            commentsSection.appendChild(filterButtonNegative);

            let filterButtonPositiveLanguage = document.createElement("button");
            filterButtonPositiveLanguage.id = 4;
            filterButtonPositiveLanguage.className = "filter-button";
            filterButtonPositiveLanguage.textContent = "Filtro: Comentarios positivos";
            filterButtonPositiveLanguage.addEventListener("click", filterComments);

            commentsSection.appendChild(filterButtonPositiveLanguage);

            let filterButtonNegativeLanguage = document.createElement("button");
            filterButtonNegativeLanguage.id = 5;
            filterButtonNegativeLanguage.className = "filter-button";
            filterButtonNegativeLanguage.textContent = "Filtro: Comentarios negativos";
            filterButtonNegativeLanguage.addEventListener("click", filterComments);

            commentsSection.appendChild(filterButtonNegativeLanguage);

            let buttonOrderByDateAsc = document.createElement("button");
            buttonOrderByDateAsc.id = 1;
            buttonOrderByDateAsc.className = "filter-button";
            buttonOrderByDateAsc.textContent = "Ordenar: Más antiguos";
            buttonOrderByDateAsc.addEventListener("click", orderByDateAsc);

            commentsSection.appendChild(buttonOrderByDateAsc);

            let buttonOrderByDateDesc = document.createElement("button");
            buttonOrderByDateDesc.id = 2;
            buttonOrderByDateDesc.className = "filter-button";
            buttonOrderByDateDesc.textContent = "Ordenar: Más recientes";
            buttonOrderByDateDesc.addEventListener("click", orderByDateDesc);

            commentsSection.appendChild(buttonOrderByDateDesc);
        }

        for (let i = 0; i < commentsSorted.length; i++) {

            if (userLanguages != null) {
                if (commentFilterMode == 1 && (userLanguages[0]['id_idioma_principal'] != commentsSorted[i]['id_idioma_comentario'] && userLanguages[0]['id_idioma_secundario'] != commentsSorted[i]['id_idioma_comentario'])) {
                    continue;
                }
                else if (commentFilterMode == 2 && ((userLanguages[0]['id_idioma_principal'] != commentsSorted[i]['id_idioma_comentario'] && userLanguages[0]['id_idioma_secundario'] != commentsSorted[i]['id_idioma_comentario']) || commentsSorted[i]['valoracion'] == "negativa")) {
                    continue;
                }
                else if (commentFilterMode == 3 && ((userLanguages[0]['id_idioma_principal'] != commentsSorted[i]['id_idioma_comentario'] && userLanguages[0]['id_idioma_secundario'] != commentsSorted[i]['id_idioma_comentario']) || commentsSorted[i]['valoracion'] == "positiva")) {
                    continue;
                }
                else if (commentFilterMode == 4 && (commentsSorted[i]['valoracion'] == "negativa")) {
                    continue;
                }
                else if (commentFilterMode == 5 && (commentsSorted[i]['valoracion'] == "positiva")) {
                    continue;
                }
            }

            let comment = document.createElement("div");
            comment.className = "comment";

            let commentUser = document.createElement("p");
            commentUser.className = "comment-user";
            commentUser.textContent = commentsSorted[i]['nickname'];

            comment.appendChild(commentUser);

            let commentFecha = document.createElement("p");
            commentFecha.className = "comment-user";
            commentFecha.textContent = "Fecha publicación: " + commentsSorted[i]['fechaPublicacion'];

            comment.appendChild(commentFecha);

            let commentRating = document.createElement("p");
            commentRating.className = "comment-rating";
            commentRating.textContent = "Valoración: " + commentsSorted[i]['valoracion'];

            comment.appendChild(commentRating);

            let commentText = document.createElement("p");
            commentText.className = "comment-text";
            commentText.textContent = commentsSorted[i]['comentario'];

            comment.appendChild(commentText);

            commentsSection.appendChild(comment);
        }
    }
}

async function buyGame() {
    let buy_bought = await checkField_2(gameName, userNickname, "buy_game", "../AJAX/gameData.php");
    bought = await checkField_2(gameName, userNickname, "check_bought", "../AJAX/gameData.php");
    if (bought) {
        if (wishlist) {
            let comment = await checkField_2(gameName, userNickname, "remove_wishlist", "../AJAX/gameData.php");
            wishlist = await checkField_2(gameName, userNickname, "get_wishlist", "../AJAX/gameData.php");
        }
    }
    await setUpPurchaseSection();
    await setUpComments();
}

async function submitComment() {
    let rating
    if (like) {
        rating = "positiva";
    }
    else {
        rating = "negativa";
    }
    let commentValue = document.querySelector("#comment-input-new").value;
    let comment = await checkField_4(gameName, userNickname, rating, commentValue, "create_comment", "../AJAX/gameData.php");
    userReview = await checkField_2(gameName, userNickname, "user_comment", "../AJAX/gameData.php");
    comments = await checkField_1(gameName, "get_comments", "../AJAX/gameData.php");
    commentsSorted = await checkField_1(gameName, "get_comments", "../AJAX/gameData.php");
    if (isFiltered == 1) {
        await orderByDateAsc();
    }
    else if (isFiltered == 2) {
        await orderByDateDesc();
    }
    positive = await checkField_1(gameName, "positive", "../AJAX/gameData.php");
    await setUpGameInfo();
    await setUpComments();
}

async function saveComment() {
    let rating
    if (like) {
        rating = "positiva";
    }
    else {
        rating = "negativa";
    }
    let commentValue = document.querySelector("#comment-input").value;
    let comment = await checkField_4(gameName, userNickname, rating, commentValue, "update_comment", "../AJAX/gameData.php");
    userReview = await checkField_2(gameName, userNickname, "user_comment", "../AJAX/gameData.php");
    comments = await checkField_1(gameName, "get_comments", "../AJAX/gameData.php");
    commentsSorted = await checkField_1(gameName, "get_comments", "../AJAX/gameData.php");
    if (isFiltered == 1) {
        await orderByDateAsc();
    }
    else if (isFiltered == 2) {
        await orderByDateDesc();
    }
    positive = await checkField_1(gameName, "positive", "../AJAX/gameData.php");
    await setUpGameInfo();
    await setUpComments();
}

async function deleteComment() {
    let comment = await checkField_2(gameName, userNickname, "update_comment", "../AJAX/gameData.php");
    userReview = await checkField_2(gameName, userNickname, "delete_comment", "../AJAX/gameData.php");
    comments = await checkField_1(gameName, "get_comments", "../AJAX/gameData.php");
    commentsSorted = await checkField_1(gameName, "get_comments", "../AJAX/gameData.php");
    if (isFiltered == 1) {
        await orderByDateAsc();
    }
    else if (isFiltered == 2) {
        await orderByDateDesc();
    }
    positive = await checkField_1(gameName, "positive", "../AJAX/gameData.php");
    await setUpGameInfo();
    await setUpComments();
}

async function editComment() {
    document.getElementById("comment-input").disabled = !document.getElementById("comment-input").disabled;
    document.getElementById("save-comment-button").disabled = !document.getElementById("save-comment-button").disabled;
    document.getElementById("delete-comment-button").disabled = !document.getElementById("delete-comment-button").disabled;
    document.getElementById("like-button-new").disabled = !document.getElementById("like-button-new").disabled;
}

async function likeDislike() {
    like = !like;
    if (like) {
        document.getElementById("likeDislike").textContent = "thumb_up";
    }
    else {
        document.getElementById("likeDislike").textContent = "thumb_down";
    }
    
}

async function addToWishlist() {
    let comment = await checkField_2(gameName, userNickname, "add_wishlist", "../AJAX/gameData.php");
    wishlist = await checkField_2(gameName, userNickname, "get_wishlist", "../AJAX/gameData.php");
    await setUpPurchaseSection();
}

async function removeToWishlist() {
    let comment = await checkField_2(gameName, userNickname, "remove_wishlist", "../AJAX/gameData.php");
    wishlist = await checkField_2(gameName, userNickname, "get_wishlist", "../AJAX/gameData.php");
    await setUpPurchaseSection();
}

async function filterComments() {
    commentsSorted = await checkField_1(gameName, "get_comments", "../AJAX/gameData.php");
    await (isFiltered = 0);
    await (commentFilterMode = parseInt(this.id));
    await setUpComments();
}

async function pauseVideoIfPlaying() {
    
    let videos = await document.getElementsByClassName("video-carousel");
    for (let pos = 0; pos < videos.length; pos++) {

        videos[pos].pause();
    }
}

async function goToShopFilterByCategory() {

    window.location.href = './game.php?error=changeURLInLine585';
}