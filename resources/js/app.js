import "./bootstrap";

// resources/js/app.js
document.addEventListener("alpine:init", () => {
    Alpine.data("globalHelpers", () => ({
        showAlert(message, type = "info") {
            const alertContainer = document.getElementById("alert-container");
            const alertId = "alert-" + Date.now();

            const alertHTML = `
                <div id="${alertId}" class="alert alert-${type} mb-2" x-data="{ show: true }" 
                     x-show="show" x-transition>
                    <span>${message}</span>
                    <button class="btn btn-sm btn-ghost" @click="show = false">×</button>
                </div>
            `;

            alertContainer.insertAdjacentHTML("beforeend", alertHTML);

            setTimeout(() => {
                const alert = document.getElementById(alertId);
                if (alert) alert.remove();
            }, 5000);
        },

        async confirmDelete(message = "Bạn có chắc chắn muốn xóa item này?") {
            return new Promise((resolve) => {
                this.$dispatch("open-confirm-modal", {
                    title: "Xác nhận xóa",
                    message: message,
                    callback: () => resolve(true),
                });

                // Auto resolve false after 10 seconds
                setTimeout(() => resolve(false), 10000);
            });
        },
    }));
});
