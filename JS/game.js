let params = new URLSearchParams(document.location.search);
var sesionValue;
var element;
var formData;
var response;
var data;

var bought;

async function checkField_1(value_1, elementId, action) {

    if (!value_1.trim()) {
        return;
    }
    
    formData = new FormData();
    formData.append("value", value_1);
    formData.append("action", action);

    try {
        response = await fetch("../AJAX/game.php", {
            method: "POST",
            body: formData
        });

        data = await response.json();

        if (data.ok && data.exists) {
            // Handle successful validation
            console.log("Validation successful");
        }
        else {
            // Handle failed validation
            console.log("Validation failed");
        }

    }
    catch (error) {
        // Handle error
    }
}

async function checkField_2(value_1, value_2, element, action) {

    if (!value_1.trim()) {
        return;
    }

    if (!value_2.trim()) {
        return;
    }

    formData = new FormData();
    formData.append("value_1", value_1);
    formData.append("value_2", value_2);
    formData.append("action", action);

    try {
        response = await fetch("../AJAX/game.php", {
            method: "POST",
            body: formData
        });

        if (response != "No data found!") {

            data = await response.json();
            if (element === "bought") {
                bought = data;
            }

            console.log(data);
        }
        else {
            console.log("No data found!");
        }
    }
    catch (error) {
        console.log(error);
    }
}

$(window).on("load", function() {
    sesionValue = $("#hdnSession").data('value');
    console.log("Session value: " + sesionValue);
    checkField_2(params.get("nameGame"), sesionValue, "bought", "check_bought");
});

$("button").on("click", function() {
    console.log(bought);
});