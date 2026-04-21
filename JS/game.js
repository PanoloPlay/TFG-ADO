let params = new URLSearchParams(document.location.search);
var sesionValue;
var element;
var formData;
var response;
var data;

var gameName;

var game;
var bought;
var userReview;
var comments;
var positive;

$(window).on("load", async function() {
    if (await getAllData()) {
        await setUp();
    }
});

async function checkField_1(value_1, action) {

    if (!value_1.trim()) {

        return null;
    }

    formData = new FormData();
    formData.append("value_1", value_1);
    formData.append("action", action);

    try {
        response = await fetch("../AJAX/gameData.php", {
            method: "POST",
            body: formData
        });

        if ((data = await response.json()) != "No data found!") {

            return data;
        }
        else {

            return null;
        }
    }
    catch (error) {

        console.log(error);
        return null;
    }
}

async function checkField_2(value_1, value_2, action) {

    if (!value_1.trim()) {

        return null;
    }

    if (!value_2.trim()) {

        return null;
    }

    formData = new FormData();
    formData.append("value_1", value_1);
    formData.append("value_2", value_2);
    formData.append("action", action);

    try {
        response = await fetch("../AJAX/gameData.php", {
            method: "POST",
            body: formData
        });

        if ((data = await response.json()) != "No data found!") {

            return data;
        }
        else {

            return null;
        }
    }
    catch (error) {

        console.log(error);
        return null;
    }
}

async function checkField_3(value_1, value_2, value_3, action) {

    if (!value_1.trim()) {

        return null;
    }

    if (!value_2.trim()) {

        return null;
    }

    if (!value_3.trim()) {

        return null;
    }

    formData = new FormData();
    formData.append("value_1", value_1);
    formData.append("value_2", value_2);
    formData.append("value_3", value_3);
    formData.append("action", action);

    try {
        response = await fetch("../AJAX/gameData.php", {
            method: "POST",
            body: formData
        });

        if ((data = await response.json()) != "No data found!") {

            return data;
        }
        else {

            return null;
        }
    }
    catch (error) {

        console.log(error);
        return null;
    }
}

async function checkField_4(value_1, value_2, value_3, value_4, action) {

    if (!value_1.trim()) {

        return null;
    }

    if (!value_2.trim()) {

        return null;
    }

    if (!value_3.trim()) {

        return null;
    }

    if (!value_4.trim()) {

        return null;
    }

    formData = new FormData();
    formData.append("value_1", value_1);
    formData.append("value_2", value_2);
    formData.append("value_3", value_3);
    formData.append("value_4", value_4);
    formData.append("action", action);

    try {
        response = await fetch("../AJAX/gameData.php", {
            method: "POST",
            body: formData
        });

        if ((data = await response.json()) != "No data found!") {

            return data;
        }
        else {

            return null;
        }
    }
    catch (error) {

        console.log(error);
        return null;
    }
}


async function getAllData() {
    sesionValue = $("#hdnSession").data('value');
    gameName = params.get("nameGame");

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

    game = await checkField_1(gameName, "get_game");
    if (!game) {
        
        window.location.href = './game.php?error=GameNotFound';
        return false;
    }
    console.log(game);

    if (sesionValue != null) {

        bought = await checkField_2(gameName, sesionValue, "check_bought");
        console.log(bought);

        if (bought != null) {
            userReview = await checkField_2(gameName, sesionValue, "user_comment");
            console.log(userReview);
        }
    }

    comments = await checkField_1(gameName, "get_comments");
    console.log(comments);

    positive = await checkField_1(gameName, "positive");
    console.log(positive);

    return true;
}

async function setUp() {

    document.getElementById("big-game-title").textContent = game[0]['nombre_juego'];
    document.getElementById("game-description").textContent = game[0]['descripcion'];
    document.getElementById("game-developer").textContent = "Desarrollador: " + game[0]['desarrollador'];
    let date = new Date(game[0]['fecha_publicacion']);
    document.getElementById("game-release-date").textContent = "Fecha de publicación: " + date.toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' });

    setUpGameInfo();
    setUpPurchaseSection();
    setUpComments();
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

function setUpPurchaseSection() {

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
            window.location.href = './game.php?error=changeURLInLine337';
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

        if (game[0]['descuento'] == 0) {
            
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

    if (comments == null) {

        let noComments = document.createElement("div");
        noComments.className = "no-comments";
        noComments.textContent = "Aún no hay comentarios para este juego.";

        commentsSection.appendChild(noComments);
    }
    else {
        for (let i = 0; i < comments.length; i++) {

            let comment = document.createElement("div");
            comment.className = "comment";

            let commentUser = document.createElement("p");
            commentUser.className = "comment-user";
            commentUser.textContent = comments[i]['nickname'];

            comment.appendChild(commentUser);

            let commentRating = document.createElement("p");
            commentRating.className = "comment-rating";
            commentRating.textContent = "Valoración: " + comments[i]['valoracion'];

            comment.appendChild(commentRating);

            let commentText = document.createElement("p");
            commentText.className = "comment-text";
            commentText.textContent = comments[i]['comentario'];

            comment.appendChild(commentText);

            commentsSection.appendChild(comment);
        }
    }
}

async function buyGame() {
    console.log("Comprar juego: " + game[0]['nombre_juego']);
    let buy_bought = await checkField_2(gameName, sesionValue, "buy_game");
    bought = await checkField_2(gameName, sesionValue, "check_bought");
    await setUpPurchaseSection();
    await setUpComments();
}

async function submitComment() {
    console.log("Comentario enviado: " + document.querySelector("#comment-input-new").value);
    let commentValue = document.querySelector("#comment-input-new").value;
    let comment = await checkField_4(gameName, sesionValue, "negativa", commentValue, "create_comment");
    userReview = await checkField_2(gameName, sesionValue, "user_comment");
    comments = await checkField_1(gameName, "get_comments");
    positive = await checkField_1(gameName, "positive");
    await setUpGameInfo();
    await setUpComments();
}

async function saveComment() {
    console.log("Comentario editado: " + document.querySelector("#comment-input").value);
    let commentValue = document.querySelector("#comment-input").value;
    let comment = await checkField_4(gameName, sesionValue, "positiva", commentValue, "update_comment");
    userReview = await checkField_2(gameName, sesionValue, "user_comment");
    comments = await checkField_1(gameName, "get_comments");
    positive = await checkField_1(gameName, "positive");
    await setUpGameInfo();
    await setUpComments();
}

async function deleteComment() {
    console.log("Comentario borrado");
    let comment = await checkField_2(gameName, sesionValue, "update_comment");
    userReview = await checkField_2(gameName, sesionValue, "delete_comment");
    comments = await checkField_1(gameName, "get_comments");
    positive = await checkField_1(gameName, "positive");
    await setUpGameInfo();
    await setUpComments();
}

async function editComment() {
    console.log("editar");
    document.getElementById("comment-input").disabled = !document.getElementById("comment-input").disabled;
    document.getElementById("save-comment-button").disabled = !document.getElementById("save-comment-button").disabled;
    document.getElementById("delete-comment-button").disabled = !document.getElementById("delete-comment-button").disabled;
}