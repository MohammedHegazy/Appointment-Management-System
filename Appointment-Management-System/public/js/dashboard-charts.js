(function () {
    function buildDefaultOptions(type) {
        var options = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: type !== "line",
                    position: "bottom",
                },
            },
        };

        if (type !== "doughnut" && type !== "pie") {
            options.scales = {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    grid: { color: "rgba(100, 116, 139, 0.18)" },
                },
                x: {
                    grid: { display: false },
                },
            };
        }

        return options;
    }

    document.addEventListener("DOMContentLoaded", function () {
        if (typeof window.Chart === "undefined") {
            return;
        }

        var chartEls = document.querySelectorAll(".js-chart");
        chartEls.forEach(function (el) {
            var raw = el.getAttribute("data-chart");
            if (!raw) {
                return;
            }

            try {
                var config = JSON.parse(raw);
                config.options = Object.assign(buildDefaultOptions(config.type || "line"), config.options || {});
                new window.Chart(el, config);
            } catch (error) {
                // no-op: invalid chart payload should not break dashboard rendering
            }
        });
    });
})();
