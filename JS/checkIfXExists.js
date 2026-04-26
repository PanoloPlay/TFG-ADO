var element;
var formData;
var response;
var data;

async function checkField_1(value_1, action, file) {

    if (!value_1.trim()) {

        return null;
    }

    formData = new FormData();
    formData.append("value_1", value_1);
    formData.append("action", action);

    try {
        response = await fetch(file, {
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