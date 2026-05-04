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

let librarySorted = null;
let isOrdered = false;

$(window).on('load', async function() {
    if (await getAllData()) {
        await setUp();
    }
    else {
        window.location.href = "../AUTH/login.php";
    }
});

async function getAllData() {

    await (userNickname = await $("#hdnSession").data('value'));

    user = await checkField_1(userNickname, "get_user", "../AJAX/libraryGameData.php");
    if (user == null) {
        return false;
    }
    library = await checkField_1(userNickname, "get_library", "../AJAX/libraryGameData.php");
    console.log(library);

    if (library != null) {
        librarySorted = await checkField_1(userNickname, "get_library", "../AJAX/libraryGameData.php");
        //await (librarySorted[0]["nombre_juego"] = "Zelda: Breath of the Wild");
        //await (librarySorted[0]["nombre_juego"] = "Asphalt 9: Legends");
        //await (librarySorted[1]["nombre_juego"] = "Asphalt 9: Legends");
        //await orderByNameAsc();
        //await orderByNameDesc();
        //await orderByDateAsc();
        //await orderByDateDesc();
        //await orderByPriceAsc();
        //await orderByPriceDesc();
        //await orderByDiscountAsc();
        //await orderByDiscountDesc();
        //await orderByCommentAmountAsc();
        //await orderByCommentAmountDesc();
        //await orderByPositiveReviewsAmountAsc();
        //await orderByPositiveReviewsAmountDesc();
        //await orderByPositiveRatioAsc();
        //await orderByPositiveRatioDesc();
    }

    return true;
}

async function orderByNameAsc() {
    await (librarySorted = await librarySorted.sort((a, b) => a.nombre_juego.localeCompare(b.nombre_juego)));
    console.log(librarySorted);
}

async function orderByNameDesc() {
    await (librarySorted = await librarySorted.sort((a, b) => b.nombre_juego.localeCompare(a.nombre_juego)));
    console.log(librarySorted);
}

async function orderByDateAsc() {
    await (librarySorted = await librarySorted.sort((a, b) => a.fecha_publicacion.localeCompare(b.fecha_publicacion)));
    console.log(librarySorted);
}

async function orderByDateDesc() {
    await (librarySorted = await librarySorted.sort((a, b) => b.fecha_publicacion.localeCompare(a.fecha_publicacion)));
    console.log(librarySorted);
}

async function orderByPriceAsc() {
    await (librarySorted = await librarySorted.sort((a, b) => a.precio.localeCompare(b.precio)));
    console.log(librarySorted);
}

async function orderByPriceDesc() {
    await (librarySorted = await librarySorted.sort((a, b) => b.precio.localeCompare(a.precio)));
    console.log(librarySorted);
}

async function orderByDiscountAsc() {
    await (librarySorted = await librarySorted.sort((a, b) => a.descuento.localeCompare(b.descuento)));
    console.log(librarySorted);
}

async function orderByDiscountDesc() {
    await (librarySorted = await librarySorted.sort((a, b) => b.descuento.localeCompare(a.descuento)));
    console.log(librarySorted);
}

async function orderByCommentAmountAsc() {
    await (librarySorted = await librarySorted.sort((a, b) => (a.valoraciones.toString()).localeCompare(b.valoraciones.toString())));
    console.log(librarySorted);
}

async function orderByCommentAmountDesc() {
    await (librarySorted = await librarySorted.sort((a, b) => (b.valoraciones.toString()).localeCompare(a.valoraciones.toString())));
    console.log(librarySorted);
}

async function orderByPositiveReviewsAmountAsc() {
    await (librarySorted = await librarySorted.sort((a, b) => (a.valoraciones_positivas.toString()).localeCompare(b.valoraciones_positivas.toString())));
    console.log(librarySorted);
}

async function orderByPositiveReviewsAmountDesc() {
    await (librarySorted = await librarySorted.sort((a, b) => (b.valoraciones_positivas.toString()).localeCompare(a.valoraciones_positivas.toString())));
    console.log(librarySorted);
}

async function orderByPositiveRatioAsc() {
    await (librarySorted = await librarySorted.sort((a, b) => a.valoracion_media.localeCompare(b.valoracion_media)));
    console.log(librarySorted);
}

async function orderByPositiveRatioDesc() {
    await (librarySorted = await librarySorted.sort((a, b) => b.valoracion_media.localeCompare(a.valoracion_media)));
    console.log(librarySorted);
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
        gameSideImg.src = "../IMG/juegos/" + gameSideIco + "/icons/icon.svg";
        gameSideImg.alt = gameSideName;
        gameSideImg.width = 25;
        gameSideImg.height = 25;

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
    //libraryMain.innerHTML = "<h1>Juego seleccionado: " + library[game.id]["nombre_juego"] + "</h1>";
    let gameMainIco = await library[game.id]["nombre_juego"].replaceAll(" ", "_");

    gameMainIco = await gameMainIco.replaceAll(".", "");
    gameMainIco = await gameMainIco.replaceAll(",", "");
    gameMainIco = await gameMainIco.replaceAll(":", "");
    gameMainIco = await gameMainIco.replaceAll(";", "");

    let mainGameIMG = document.createElement("img");
    mainGameIMG.src = "../IMG/juegos/" + gameMainIco + "/icons/banner.svg";
    mainGameIMG.alt = library[game.id]["nombre_juego"];
    mainGameIMG.className = "d-block w-100";
    mainGameIMG.height = 200;
    
    libraryMainHeader.appendChild(mainGameIMG);

    let mainDowlaodButton = document.createElement("button");
    mainDowlaodButton.id = "download-button";
    mainDowlaodButton.className = "download-button d-block w-100";
    mainDowlaodButton.textContent = "Descargar: " + library[game.id]["nombre_juego"];
    mainDowlaodButton.addEventListener("click", async function() {
        await dowloadGame();
    });

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
    console.log(achievements_unknown);
    console.log(achievements_obtained);

    if (achievements_obtained != null) {
        for (let i = 0; i < achievements_obtained.length; i++) {

            let achivementIcoName = await achievements_obtained[i]["nombre_logro"].replaceAll(" ", "_");

            achivementIcoName = await achivementIcoName.replaceAll(".", "");
            achivementIcoName = await achivementIcoName.replaceAll(",", "");
            achivementIcoName = await achivementIcoName.replaceAll(":", "");
            achivementIcoName = await achivementIcoName.replaceAll(";", "");

            let achievementObtained = document.createElement("div");
            achievementObtained.className = "achievement-obtained d-flex flex-column justify-content-center align-items-center";

            let achievementObtainedImg = document.createElement("img");
            achievementObtainedImg.src = "../IMG/juegos/" + gameIcoPath + "/achivements/" + achivementIcoName + ".svg";
            achievementObtainedImg.alt = achievements_obtained[i]["nombre_logro"];
            achievementObtainedImg.width = 50;
            achievementObtainedImg.height = 50;

            achievementObtained.appendChild(achievementObtainedImg);

            libraryMainRecentAchievements.appendChild(achievementObtained);
        }
    }

    if (achievements_unknown != null) {
        for (let i = 0; i < achievements_unknown.length; i++) {

            let achivementIcoName = await achievements_unknown[i]["nombre_logro"].replaceAll(" ", "_");

            achivementIcoName = await achivementIcoName.replaceAll(".", "");
            achivementIcoName = await achivementIcoName.replaceAll(",", "");
            achivementIcoName = await achivementIcoName.replaceAll(":", "");
            achivementIcoName = await achivementIcoName.replaceAll(";", "");
            
            let achievementObtained = document.createElement("div");
            achievementObtained.className = "achievement-obtained d-flex flex-column justify-content-center align-items-center";

            let achievementObtainedImg = document.createElement("img");
            achievementObtainedImg.src = "../IMG/juegos/" + gameIcoPath + "/achivements/" + achivementIcoName + ".svg";
            achievementObtainedImg.style.filter = "grayscale(100%)";
            achievementObtainedImg.alt = achievements_unknown[i]["nombre_logro"];
            achievementObtainedImg.width = 50;
            achievementObtainedImg.height = 50;

            achievementObtained.appendChild(achievementObtainedImg);

            libraryMainRecentAchievements.appendChild(achievementObtained);
        }
    }
}

async function setUpMainLibraryList() {
    libraryMainAllGames.innerHTML = "<h1>Selecciona un juego</h1>";
}

async function dowloadGame() {
    alert("Descargando juego...");
}