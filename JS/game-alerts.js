document.addEventListener("DOMContentLoaded", function () {
    const alertContainer = document.getElementById("gameAlertContainer");

    if (!alertContainer) {
        return;
    }

    function normalizeType(type) {
        const t = String(type || "info").toLowerCase();

        if (t === "danger") return "error";
        if (t === "error") return "error";
        if (t === "success") return "success";
        if (t === "warning") return "warning";
        if (t === "info") return "info";

        return "info";
    }

    function createAlert(message, type) {
        const text = String(message || "").trim();
        if (text === "") {
            return;
        }

        const alert = document.createElement("div");
        const alertType = normalizeType(type);

        alert.className = `alert-custom alert-${alertType}`;
        alert.textContent = text;

        alertContainer.appendChild(alert);

        setTimeout(() => {
            alert.classList.add("is-hiding");

            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    }

    const storedAlert = sessionStorage.getItem("gameAlert");
    if (storedAlert) {
        try {
            const parsed = JSON.parse(storedAlert);
            createAlert(parsed.message || "", parsed.type || "info");
        } catch (error) {
            console.error("No se pudo leer el aviso guardado.");
        }
        sessionStorage.removeItem("gameAlert");
    }

    const visible = alertContainer.getAttribute("data-alert-visible");
    const alertMessage = alertContainer.getAttribute("data-alert-message") || "";
    const alertType = alertContainer.getAttribute("data-alert-type") || "info";

    if (visible === "1" && alertMessage.trim() !== "") {
        createAlert(alertMessage, alertType);

        const url = new URL(window.location.href);
        if (url.searchParams.has("game_message") || url.searchParams.has("game_message_type")) {
            url.searchParams.delete("game_message");
            url.searchParams.delete("game_message_type");
            history.replaceState(null, "", url.toString());
        }
    }
});

function showGameAlert(message, type = "info") {
    const container = document.getElementById("gameAlertContainer");
    if (!container) {
        return;
    }

    const text = String(message || "").trim();
    if (text === "") {
        return;
    }

    const normalizeType = (t) => {
        const x = String(t || "info").toLowerCase();
        if (x === "danger" || x === "error") return "error";
        if (x === "success") return "success";
        if (x === "warning") return "warning";
        return "info";
    };

    container.innerHTML = "";

    const alert = document.createElement("div");
    alert.className = `alert-custom alert-${normalizeType(type)}`;
    alert.textContent = text;
    container.appendChild(alert);

    setTimeout(() => {
        alert.classList.add("is-hiding");
        setTimeout(() => alert.remove(), 500);
    }, 5000);
}

function queueGameAlert(message, type = "success") {
    sessionStorage.setItem(
        "gameAlert",
        JSON.stringify({
            message: String(message || ""),
            type: String(type || "success")
        })
    );
}