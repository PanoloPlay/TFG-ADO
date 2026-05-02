let params = new URLSearchParams(document.location.search);

let errorSection = document.getElementById("error-section");
let librarySide = document.getElementById("library-side");
let libraryMain = document.getElementById("library-main");

let gameName = null;
let userNickname;

let user;
let library;
let game;

$(window).on('load', async function() {
    if (await getAllData()) {
        await setUp();
    }
    else {
        window.location.href = "../AUTH/login.php";
    }
});

async function getAllData() {

    userNickname = await $("#hdnSession").data('value');

    user = await checkField_1(userNickname, "get_user", "../AJAX/libraryGameData.php");
    if (user == null) {
        return false;
    }
    library = await checkField_1(userNickname, "get_library", "../AJAX/libraryGameData.php");
    return true;
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
            });
        }
        else {
            gameSide.className = "game-side d-block w-100 d-flex justify-content-start align-items-center";

            gameSide.addEventListener("click", async function() {
                gameName = gameSideName;
                await setUpSideLibrary();
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

}



async function setUpGame() {

}

async function test() {
    console.log(gameName);
}