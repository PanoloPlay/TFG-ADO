async function checkField(field, value, messageId) {
    const msg = document.getElementById(messageId);
    if (!msg) return;

    if (!value.trim()) {
        msg.textContent = "";
        msg.className = "field-msg";
        return;
    }

    const formData = new FormData();
    formData.append("field", field);
    formData.append("value", value);

    try {
        const response = await fetch("../AJAX/check_user.php", {
            method: "POST",
            body: formData
        });

        const data = await response.json();

        if (data.ok && data.exists) {
            msg.textContent = field === "nickname"
                ? "Ese nickname ya está en uso."
                : "Ese correo ya está en uso.";
            msg.className = "field-msg bad";
        } else {
            msg.textContent = "Disponible.";
            msg.className = "field-msg good";
        }
    } catch (error) {
        msg.textContent = "";
        msg.className = "field-msg";
    }
}

document.addEventListener("DOMContentLoaded", () => {
    const nick = document.getElementById("nickname");
    const correo = document.getElementById("correo");

    if (nick) {
        nick.addEventListener("blur", () => checkField("nickname", nick.value, "nick-msg"));
    }

    if (correo) {
        correo.addEventListener("blur", () => checkField("correo", correo.value, "mail-msg"));
    }
});