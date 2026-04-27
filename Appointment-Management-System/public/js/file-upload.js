(function () {
    function setFileName(zone, input) {
        var nameLabel = zone.querySelector(".js-file-upload-name");
        if (!nameLabel) {
            return;
        }

        if (input.files && input.files.length > 0) {
            nameLabel.textContent = input.files[0].name;
            return;
        }

        nameLabel.textContent = "No file selected";
    }

    function wireFileUpload(zone) {
        var targetSelector = zone.getAttribute("data-target");
        if (!targetSelector) {
            return;
        }

        var input = document.querySelector(targetSelector);
        var trigger = zone.querySelector(".js-file-upload-trigger");
        if (!input || !trigger) {
            return;
        }

        trigger.addEventListener("click", function () {
            input.click();
        });

        input.addEventListener("change", function () {
            setFileName(zone, input);
            zone.classList.remove("is-dragging");
        });

        ["dragenter", "dragover"].forEach(function (eventName) {
            trigger.addEventListener(eventName, function (event) {
                event.preventDefault();
                event.stopPropagation();
                zone.classList.add("is-dragging");
            });
        });

        ["dragleave", "dragend", "drop"].forEach(function (eventName) {
            trigger.addEventListener(eventName, function (event) {
                event.preventDefault();
                event.stopPropagation();
                zone.classList.remove("is-dragging");
            });
        });

        trigger.addEventListener("drop", function (event) {
            if (!event.dataTransfer || !event.dataTransfer.files || !event.dataTransfer.files.length) {
                return;
            }

            input.files = event.dataTransfer.files;
            input.dispatchEvent(new Event("change", { bubbles: true }));
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        var zones = document.querySelectorAll(".js-file-upload");
        zones.forEach(wireFileUpload);
    });
})();
