(function () {
    function dismissToast(toast) {
        if (!toast || toast.classList.contains("is-leaving")) {
            return;
        }

        toast.classList.add("is-leaving");
        window.setTimeout(function () {
            toast.remove();
        }, 260);
    }

    function createToast(stack, message) {
        var toast = document.createElement("div");
        toast.className = "toast toast--" + message.type;

        var text = document.createElement("p");
        text.className = "toast__text";
        text.textContent = message.text;

        var close = document.createElement("button");
        close.type = "button";
        close.className = "toast__close";
        close.setAttribute("aria-label", "Close notification");
        close.innerHTML = "&times;";
        close.addEventListener("click", function () {
            dismissToast(toast);
        });

        toast.appendChild(text);
        toast.appendChild(close);
        stack.appendChild(toast);

        window.setTimeout(function () {
            dismissToast(toast);
        }, 4200);
    }

    document.addEventListener("DOMContentLoaded", function () {
        var stack = document.getElementById("toast-stack");
        var messages = window.AppToasts || [];

        if (!stack || !Array.isArray(messages)) {
            return;
        }

        messages.forEach(function (message) {
            if (!message || !message.type || !message.text) {
                return;
            }
            createToast(stack, message);
        });
    });
})();
