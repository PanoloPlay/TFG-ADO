let params = new URLSearchParams(document.location.search);

let errorSection = document.getElementById("error-section");
let librarySide = document.getElementById("library-side-body");
let libraryMainHeader = document.getElementById("library-main-header");
let libraryMainBody = document.getElementById("library-main-body");
let libraryMainAllGames = document.getElementById("library-main-all-games");
let libraryMainRecentAchievements = document.getElementById("library-main-achievements-recent");
let libraryMainAllAchievements = document.getElementById("library-main-achievements-all");

let gameName = null;
let userNickname;

let user;
let library;
let game;

let achievements_unknown;
let achievements_obtained;
let achievements_obtainedRecent;

let librarySorted = null;
let isOrdered = 0;
let orderType = "Ninguno";

let fileExists = false;
let gameEXE;

function isValidImage(src) {
  return new Promise((resolve) => {
    const img = new Image();

    img.onload = () => resolve(true);
    img.onerror = () => resolve(false);

    img.src = src;
  });
}

async function doesFileExists(filePath) {

    await ($.ajax({

        url: "../AJAX/libraryGameData.php",
        type: "POST",
        async: true,

        data:{

			action:"fileExists",
            obj: filePath
		}
    }).done(function(respuesta){
        if (respuesta == "exists") {
            fileExists = true;
        }
        else {
            fileExists = false;
        }
    }));
}

async function getFileName(filePath) {

    await ($.ajax({

        url: "../AJAX/libraryGameData.php",
        type: "POST",
        async: true,

        data:{

			action:"getFileName",
            obj: filePath
		}
    }).done(function(respuesta){
        if (respuesta != "Not Found") {
            gameEXE = respuesta;
        }
        else {
            gameEXE = "";
        }
    }));
}

$(window).on('load', async function() {
    await (errorSection.style.display = "none");
    if (await getAllData()) {
        await setUp();
    }
    else {
        await (errorSection.style.display = "block");
    }
});

async function getAllData() {

    await (userNickname = await $("#hdnSession").data('value'));

    user = await checkField_1(userNickname, "get_user", "../AJAX/libraryGameData.php");
    if (user == null) {
        return false;
    }
    library = await checkField_1(userNickname, "get_library", "../AJAX/libraryGameData.php");

    if (library != null) {
        librarySorted = await checkField_1(userNickname, "get_library", "../AJAX/libraryGameData.php");
        return true;
    }
    else {
        return false;
    }
    
}

async function orderByNone() {
    await (librarySorted = await library);
}

async function orderByNameAsc() {
    await (librarySorted = await librarySorted.sort((a, b) => a.nombre_juego.localeCompare(b.nombre_juego)));
}

async function orderByNameDesc() {
    await (librarySorted = await librarySorted.sort((a, b) => b.nombre_juego.localeCompare(a.nombre_juego)));
}

async function orderByDateAsc() {
    await (librarySorted = await librarySorted.sort((a, b) => a.fecha_publicacion.localeCompare(b.fecha_publicacion)));
}

async function orderByDateDesc() {
    await (librarySorted = await librarySorted.sort((a, b) => b.fecha_publicacion.localeCompare(a.fecha_publicacion)));
}

async function orderByPriceAsc() {
    await (librarySorted = await librarySorted.sort((a, b) => a.precio.localeCompare(b.precio)));
}

async function orderByPriceDesc() {
    await (librarySorted = await librarySorted.sort((a, b) => b.precio.localeCompare(a.precio)));
}

async function orderByDiscountAsc() {
    await (librarySorted = await librarySorted.sort((a, b) => a.descuento.localeCompare(b.descuento)));
}

async function orderByDiscountDesc() {
    await (librarySorted = await librarySorted.sort((a, b) => b.descuento.localeCompare(a.descuento)));
}

async function orderByCommentAmountAsc() {
    await (librarySorted = await librarySorted.sort((a, b) => (a.valoraciones.toString()).localeCompare(b.valoraciones.toString())));
}

async function orderByCommentAmountDesc() {
    await (librarySorted = await librarySorted.sort((a, b) => (b.valoraciones.toString()).localeCompare(a.valoraciones.toString())));
}

async function orderByPositiveReviewsAmountAsc() {
    await (librarySorted = await librarySorted.sort((a, b) => (a.valoraciones_positivas.toString()).localeCompare(b.valoraciones_positivas.toString())));
}

async function orderByPositiveReviewsAmountDesc() {
    await (librarySorted = await librarySorted.sort((a, b) => (b.valoraciones_positivas.toString()).localeCompare(a.valoraciones_positivas.toString())));
}

async function orderByPositiveRatioAsc() {
    await (librarySorted = await librarySorted.sort((a, b) => a.valoracion_media.localeCompare(b.valoracion_media)));
}

async function orderByPositiveRatioDesc() {
    await (librarySorted = await librarySorted.sort((a, b) => b.valoracion_media.localeCompare(a.valoracion_media)));
}

async function setUp() {
    let getGameName = await params.get("name");

    if (getGameName != null) {
        getGameName = await getGameName.replaceAll("_", " ");
        gameName = await getGameName;
    }
    await setUpSideLibrary();
    await setUpMainLibrary();
}

async function setUpSideLibrary() {

    librarySide.innerHTML = "";

    for (let i = 0; i < library.length; i++) {

        let gameSideName = await library[i]["nombre_juego"];
        gameSideName = await gameSideName.trim();

        let gameSideIco = await gameSideName.replaceAll(" ", "_");

        gameSideIco = await gameSideIco.replaceAll(".", "");
        gameSideIco = await gameSideIco.replaceAll(",", "");
        gameSideIco = await gameSideIco.replaceAll(":", "");
        gameSideIco = await gameSideIco.replaceAll(";", "");

        let gameSide = document.createElement("button");
        gameSide.id = i;
        if (gameName == gameSideName) {

            gameSide.className = "game-side curSelected d-block w-100 d-flex justify-content-start align-items-center";

            gameSide.addEventListener("click", async function() {
                gameName = null;
                await setUpSideLibrary();
                await setUpMainLibrary();
            });
        }
        else {
            gameSide.className = "game-side d-block w-100 d-flex justify-content-start align-items-center";

            gameSide.addEventListener("click", async function() {
                gameName = gameSideName;
                await setUpSideLibrary();
                await setUpMainLibrary();
            });
        }

        let gameSideImg = document.createElement("img");
        let ico = "../MEDIA/IMG/juegos/" + library[i]["id_juego"] + "/" + "/icons/icon.ico";
        isValidImage(ico)
        .then(isValid => {
            if (isValid) {
                gameSideImg.src = ico;
            } else {
                gameSideImg.src = "../MEDIA/IMG/app_icons/loto-color.svg";
            }
        });
        gameSideImg.alt = gameSideName;
        gameSideImg.width = 25;
        gameSideImg.height = 25;
        gameSideImg.style.marginRight = "5px";

        gameSide.appendChild(gameSideImg);

        let gameSideP = document.createElement("b");
        gameSideP.textContent = gameSideName;
        gameSideP.style.fontSize = "13px";

        gameSide.appendChild(gameSideP);

        librarySide.appendChild(gameSide);
    }
}

async function setUpMainLibrary() {

    libraryMainHeader.innerHTML = "";
    libraryMainAllGames.innerHTML = "";
    libraryMainRecentAchievements.innerHTML = "";
    libraryMainAllAchievements.innerHTML = "";

    let curSelected = document.getElementsByClassName("curSelected");

    if (curSelected.length > 0) {
        await setUpGame(curSelected[0]);
    }
    else {
        await setUpMainLibraryList();
    }
}

async function setUpGame(game) {

    let gameMainIco = await library[game.id]["nombre_juego"].replaceAll(" ", "_");

    gameMainIco = await gameMainIco.replaceAll(".", "");
    gameMainIco = await gameMainIco.replaceAll(",", "");
    gameMainIco = await gameMainIco.replaceAll(":", "");
    gameMainIco = await gameMainIco.replaceAll(";", "");

    let mainGameIMG = document.createElement("img");
    let ico = "../MEDIA/IMG/juegos/" + library[game.id]["id_juego"] + "/" + "/icons/wide-cover";
    isValidImage(ico + ".jpg")
    .then(isValid => {
        if (isValid) {
            mainGameIMG.src = ico + ".jpg";
        } else {
            isValidImage(ico + ".jpeg")
            .then(isValid => {
                if (isValid) {
                    mainGameIMG.src = ico + ".jpeg";
                } else {
                    mainGameIMG.src = "../MEDIA/IMG/juegos/fallback/default.jpg";
                }
            });
        }
    });
    mainGameIMG.alt = library[game.id]["nombre_juego"];
    mainGameIMG.className = "d-block w-100";
    mainGameIMG.height = 450;
    
    libraryMainHeader.appendChild(mainGameIMG);

    let mainDowlaodButton = document.createElement("button");
    mainDowlaodButton.id = "download-button";
    mainDowlaodButton.className = "download-button d-block w-100";
    mainDowlaodButton.textContent = "Descargar: " + library[game.id]["nombre_juego"];

    
    let gamePATH = "../APPS/GAMES/" + library[game.id]["id_juego"] + "/";
    await getFileName(gamePATH);
    gamePATH = gamePATH + gameEXE;

    await (doesFileExists(gamePATH));
    if (fileExists) {
        mainDowlaodButton.addEventListener("click", async function() {
            await dowloadGame(library[game.id]["id_juego"] ,gameEXE);
        });
    }
    else {
        mainDowlaodButton.addEventListener("click", async function() {
            alert("Juego no disponible todavia...");
        });
    }

    libraryMainHeader.appendChild(mainDowlaodButton);

    setUpAchivements(game);
}

async function setUpAchivements(game) {

    var gameName = await library[game.id]["nombre_juego"];

    let gameIcoPath = await library[game.id]["nombre_juego"].replaceAll(" ", "_");

    gameIcoPath = await gameIcoPath.replaceAll(".", "");
    gameIcoPath = await gameIcoPath.replaceAll(",", "");
    gameIcoPath = await gameIcoPath.replaceAll(":", "");
    gameIcoPath = await gameIcoPath.replaceAll(";", "");

    achievements_unknown = await checkField_1(gameName, "get_logros", "../AJAX/libraryGameData.php");
    achievements_obtained = await checkField_1(gameName, "get_logros_user", "../AJAX/libraryGameData.php");
    achievements_obtainedRecent = await checkField_1(gameName, "get_logros_user", "../AJAX/libraryGameData.php");
    if (achievements_obtained != null && achievements_obtainedRecent != null) {
        await (achievements_obtainedRecent = await achievements_obtainedRecent.sort((a, b) => b.fecha_obtencion.localeCompare(a.fecha_obtencion)));
    }
    let aa = null;
    if (aa != null) {

        const achievementTypes = ['cobre', 'plata', 'oro', 'platino', 'lotus'];

        let achievementPreview = document.createElement("div");
        achievementPreview.className = "achievement-preview d-flex flex-column gap-2";

        let previewTitleColor = document.createElement("p");
        previewTitleColor.textContent = "Vista de logros - color";
        previewTitleColor.className = "achievement-preview-title";
        achievementPreview.appendChild(previewTitleColor);

        let achievementColorRow = document.createElement("div");
        achievementColorRow.className = "achievement-preview-row d-flex justify-content-start gap-2";

        let achievementGrayRow = document.createElement("div");
        achievementGrayRow.className = "achievement-preview-row d-flex justify-content-start gap-2";
        achievementGrayRow.style.filter = "grayscale(100%)";

        for (let i = 0; i < achievementTypes.length; i++) {
            let tipo = achievementTypes[i];
            let achievementColorImg = document.createElement("img");
            achievementColorImg.src = "../MEDIA/IMG/juegos/fallback/achivements/" + tipo + ".jpg";
            achievementColorImg.alt = "Logro de ejemplo " + tipo;
            achievementColorImg.width = 80;
            achievementColorImg.height = 80;
            achievementColorRow.appendChild(achievementColorImg);

            let achievementGrayImg = document.createElement("img");
            achievementGrayImg.src = "../MEDIA/IMG/juegos/fallback/achivements/" + tipo + ".jpg";
            achievementGrayImg.alt = "Logro de ejemplo " + tipo + " en blanco y negro";
            achievementGrayImg.width = 80;
            achievementGrayImg.height = 80;
            achievementGrayRow.appendChild(achievementGrayImg);
        }

        let previewTitleGray = document.createElement("p");
        previewTitleGray.textContent = "Vista de logros - blanco y negro";
        previewTitleGray.className = "achievement-preview-title";

        achievementPreview.appendChild(achievementColorRow);
        achievementPreview.appendChild(previewTitleGray);
        achievementPreview.appendChild(achievementGrayRow);

        libraryMainAllAchievements.appendChild(achievementPreview);

    }

    if (achievements_obtained != null) {
        for (let pos = 0; pos < achievements_obtained.length; pos++) {

            let rarity = achievements_obtained[pos]["rareza"] || 'default';

            let achivementIMG = document.createElement("img");
            let ico = "../MEDIA/IMG/juegos/fallback/achivements/" + rarity + ".jpg";
            isValidImage(ico)
            .then(isValid => {
                if (isValid) {
                    achivementIMG.src = ico;
                } else {
                    achivementIMG.src = "../MEDIA/IMG/juegos/fallback/default.jpg";
                }
            });
            achivementIMG.width = 85;
            achivementIMG.height = 85;
            achivementIMG.style.marginRight = "2px";

            libraryMainAllAchievements.appendChild(achivementIMG);
        }
    }

    if (achievements_unknown != null) {
        for (let pos = 0; pos < achievements_unknown.length; pos++) {

            let rarity = achievements_unknown[pos]["rareza"] || 'default';

            let achivementIMG = document.createElement("img");
            let ico = "../MEDIA/IMG/juegos/fallback/achivements/" + rarity + ".jpg";
            isValidImage(ico)
            .then(isValid => {
                if (isValid) {
                    achivementIMG.src = ico;
                } else {
                    achivementIMG.src = "../MEDIA/IMG/juegos/fallback/default.jpg";
                }
            });
            achivementIMG.width = 85;
            achivementIMG.height = 85;
            achivementIMG.style.marginRight = "2px";
            achivementIMG.className = "grayscale";

            libraryMainAllAchievements.appendChild(achivementIMG);
        }
    }

    if (achievements_obtainedRecent != null) {
        for (let i = 0; i < achievements_obtainedRecent.length; i++) {

            let br = document.createElement("br");

            libraryMainRecentAchievements.appendChild(br);

            let rarity = achievements_obtainedRecent[i]["rareza"] || 'default';

            let achievementObtained = document.createElement("div");
            achievementObtained.className = "achievement-obtained d-flex justify-content-start w-100";

            let achievementObtainedImg = document.createElement("img");
            achievementObtainedImg.src = "../MEDIA/IMG/juegos/fallback/achivements/" + rarity + ".jpg";
            achievementObtainedImg.alt = achievements_obtainedRecent[i]["nombre_logro"] + " (" + rarity + ")";
            achievementObtainedImg.width = 100;
            achievementObtainedImg.height = 100;
            achievementObtainedImg.style.marginRight = "7px";

            achievementObtained.appendChild(achievementObtainedImg);

            let achievementObtainedData = document.createElement("div");
            achievementObtainedData.className = "achievement-obtained-data";

            let achievementObtainedName = document.createElement("p");
            achievementObtainedName.className = "achievement-obtained-name";
            achievementObtainedName.textContent = achievements_obtainedRecent[i]["nombre_logro"] + " [" + achievements_obtainedRecent[i]["fecha_obtencion"] + "]";

            achievementObtainedData.appendChild(achievementObtainedName);

            let achievementObtainedDescription = document.createElement("p");
            achievementObtainedDescription.className = "achievement-obtained-description";
            achievementObtainedDescription.textContent = achievements_obtainedRecent[i]["descripcion_logro"];

            achievementObtainedData.appendChild(achievementObtainedDescription);

            achievementObtained.appendChild(achievementObtainedData);

            libraryMainRecentAchievements.appendChild(achievementObtained);
        }
    }
}

async function setUpMainLibraryList() {

    libraryMainAllGames.innerHTML = "<h1><u><em><strong>Librería</strong></em></u></h1>";

    let libraryMainAllGamesHeader = document.createElement("div");
    libraryMainAllGamesHeader.className = "library-main-all-games-header d-flex justify-content-start align-items-center";
    libraryMainAllGamesHeader.style.marginBottom = "10px";

    let libraryMainAllGamesHeaderOrder = document.createElement("div");
    libraryMainAllGamesHeaderOrder.className = "library-main-all-games-header-order dropdown";

    let libraryMainAllGamesHeaderOrderButton = document.createElement("button");
    libraryMainAllGamesHeaderOrderButton.className = "btn btn-secondary dropdown-toggle";
    libraryMainAllGamesHeaderOrderButton.type = "button";
    libraryMainAllGamesHeaderOrderButton.id = "dropdownMenuButton1";
    libraryMainAllGamesHeaderOrderButton.setAttribute("data-bs-toggle", "dropdown");
    libraryMainAllGamesHeaderOrderButton.setAttribute("aria-expanded", "false");
    libraryMainAllGamesHeaderOrderButton.textContent = "Ordenar";

    libraryMainAllGamesHeaderOrder.appendChild(libraryMainAllGamesHeaderOrderButton);

    let libraryMainAllGamesHeaderOrderMenu = document.createElement("ul");
    libraryMainAllGamesHeaderOrderMenu.className = "dropdown-menu";
    libraryMainAllGamesHeaderOrderMenu.setAttribute("aria-labelledby", "dropdownMenuButton1");

    let orderOptions = ["Ninguno", "Nombre (A-Z)", "Nombre (Z-A)", "Fecha publicación (asc)", "Fecha publicación (desc)", "Precio (asc)", "Precio (desc)", "Descuento (asc)", "Descuento (desc)", "Valoraciones cantidad (asc)", "Valoraciones cantidad (desc)", "Valoraciones positivas cantidad (asc)", "Valoraciones positivas cantidad (desc)", "Valoración positiva ratio (asc)", "Valoración positiva ratio (desc)"];
    let orderFunctions = [orderByNone, orderByNameAsc, orderByNameDesc, orderByDateAsc, orderByDateDesc, orderByPriceAsc, orderByPriceDesc, orderByDiscountAsc, orderByDiscountDesc, orderByCommentAmountAsc, orderByCommentAmountDesc, orderByPositiveReviewsAmountAsc, orderByPositiveReviewsAmountDesc, orderByPositiveRatioAsc, orderByPositiveRatioDesc];

    for (let i = 0; i < orderOptions.length; i++) {

        let orderOption = document.createElement("li");
        let orderOptionButton = document.createElement("button");
        orderOptionButton.className = "dropdown-item";
        orderOptionButton.textContent = orderOptions[i];
        orderOptionButton.addEventListener("click", async function() {
            await orderFunctions[i]();
            orderType = orderOptions[i];
            await setUpMainLibraryList();
        });

        orderOption.appendChild(orderOptionButton);
        libraryMainAllGamesHeaderOrderMenu.appendChild(orderOption);
    }

    libraryMainAllGamesHeaderOrder.appendChild(libraryMainAllGamesHeaderOrderMenu);

    libraryMainAllGamesHeader.appendChild(libraryMainAllGamesHeaderOrder);

    let libraryMainAllGamesHeaderP = document.createElement("b");
    libraryMainAllGamesHeaderP.textContent = "Ordenar por: " + orderType;
    libraryMainAllGamesHeaderP.style.margin = "0px";
    libraryMainAllGamesHeaderP.style.fontSize = "14px";
    libraryMainAllGamesHeaderP.style.marginLeft = "10px";

    libraryMainAllGamesHeader.appendChild(libraryMainAllGamesHeaderP);

    libraryMainAllGames.appendChild(libraryMainAllGamesHeader);

    let libraryMainAllGamesList = document.createElement("div");
    libraryMainAllGamesList.className = "library-main-all-games-list d-flex flex-wrap justify-content-start";

    for (let i = 0; i < librarySorted.length; i++) {

        let gameListName = await librarySorted[i]["nombre_juego"].replaceAll(" ", "_");
        gameListName = await gameListName.replaceAll(".", "");
        gameListName = await gameListName.replaceAll(",", "");
        gameListName = await gameListName.replaceAll(":", "");
        gameListName = await gameListName.replaceAll(";", "");

        let gameList = document.createElement("button");
        gameList.id = i;
        gameList.className = "game-list card";
        gameList.style.margin = "2px";
        gameList.style.width = "250px";
        gameList.style.height = "350px";

        gameList.addEventListener("click", async function() {
            gameName = librarySorted[i]["nombre_juego"];
            await setUpSideLibrary();
            await setUpMainLibrary();
        });

        let gameListImg = document.createElement("img");
        let ico = "../MEDIA/IMG/juegos/" + librarySorted[i]["id_juego"] + "/" + "/icons/cover";
        isValidImage(ico + ".jpg")
        .then(isValid => {
            if (isValid) {
                gameListImg.src = ico + ".jpg";
            } else {
                isValidImage(ico + ".jpeg")
                .then(isValid => {
                    if (isValid) {
                        gameListImg.src = ico + ".jpeg";
                    } else {
                        gameListImg.src = "../MEDIA/IMG/juegos/fallback/default.jpg";
                    }
                });
            }
        });
        gameListImg.className = "game-list-img card-img-top";
        gameListImg.alt = librarySorted[i]["nombre_juego"];
        gameListImg.width = 120;
        gameListImg.height = 280;

        gameList.appendChild(gameListImg);

        let gameListData = document.createElement("div");
        gameListData.className = "game-list-data card-body";

        let gameListDescripcion = document.createElement("p");
        gameListDescripcion.className = "game-list-descripcion card-text";

        if (orderType == "Ninguno" || orderType == "Nombre (A-Z)" || orderType == "Nombre (Z-A)") {
            gameListDescripcion.textContent = librarySorted[i]["nombre_juego"];
        } 
        if (orderType == "Fecha publicación (asc)" || orderType == "Fecha publicación (desc)") {
            gameListDescripcion.textContent = librarySorted[i]["fecha_publicacion"];
        } 
        else if (orderType == "Precio (asc)" || orderType == "Precio (desc)") {
            gameListDescripcion.innerHTML = "[<s>" + librarySorted[i]["precio"] + "€</s>] " + (librarySorted[i]["precio"] * (1 - librarySorted[i]["descuento"] / 100)).toFixed(2);
        }
        else if (orderType == "Descuento (asc)" || orderType == "Descuento (desc)") {
            gameListDescripcion.innerHTML = "[<s>" + librarySorted[i]["precio"] + "€</s>] " + (librarySorted[i]["precio"] * (1 - librarySorted[i]["descuento"] / 100)).toFixed(2) + " [" + librarySorted[i]["descuento"] + "%]";
        }
        else if (orderType == "Valoraciones cantidad (asc)" || orderType == "Valoraciones cantidad (desc)") {
            gameListDescripcion.textContent = "Valoraciones: " + librarySorted[i]["valoraciones"];
        }
        else if (orderType == "Valoraciones positivas cantidad (asc)" || orderType == "Valoraciones positivas cantidad (desc)") {
            gameListDescripcion.textContent = "Valoraciones positivas: " + librarySorted[i]["valoraciones_positivas"];
        }
        else if (orderType == "Valoración positiva ratio (asc)" || orderType == "Valoración positiva ratio (desc)") {
            gameListDescripcion.textContent = "Ratio valoración positivas: " + (librarySorted[i]["valoracion_media"] * 100).toFixed(2) + "%";
        }

        gameListData.appendChild(gameListDescripcion);

        gameList.appendChild(gameListData);

        libraryMainAllGamesList.appendChild(gameList);
    }

    libraryMainAllGames.appendChild(libraryMainAllGamesList);
}

async function dowloadGame(id, filepath) {
    let href = "../MAIN/download.php";

    if (filepath != null && filepath != "") {
        href += "?id=" + id + "&file=" + filepath;
    }

    Object.assign(document.createElement('a'), {
        target: '_blank',
        rel: 'noopener noreferrer',
        href: href,
    }).click();
}