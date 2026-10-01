import "./bootstrap";
import Alpine from "alpinejs";
Alpine.store("sidebar", {
    open: window.innerWidth >= 1024,
    collapsed: localStorage.getItem("sidebar-collapsed") === "true",

    toggle() {
        this.open = !this.open;
    },
    close() {
        this.open = false;
    },
    toggleCollapse() {
        this.collapsed = !this.collapsed;
        localStorage.setItem(
            "sidebar-collapsed",
            this.collapsed ? "true" : "false",
        );
    },
});
Alpine.data("headerApp", () => ({
    showLogin: false,
    toast: { show: false, message: "" },
    showLogoutConfirm: false,

    toggleSidebar() {
        if (window.innerWidth >= 1024) {
            Alpine.store("sidebar").toggleCollapse();
        } else {
            Alpine.store("sidebar").open = !Alpine.store("sidebar").open;
        }
    },

    showToast(message) {
        this.toast.message = message;
        this.toast.show = true;
        clearTimeout(this._toastTimer);
        this._toastTimer = setTimeout(() => {
            this.toast.show = false;
        }, 3000);
    },

    confirmLogout() {
        this.showLogoutConfirm = false;
        document.getElementById("headerLogoutForm").submit();
    },

    init() {
        const form = document.getElementById("adminLoginForm");
        if (!form) return;

        form.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            try {
                const response = await fetch(form.action, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": formData.get("_token"),
                        Accept: "application/json",
                    },
                    body: formData,
                });
                const result = await response.json();
                if (result.success) {
                    this.showLogin = false;
                    window.location.href = result.redirect;
                } else {
                    this.showToast(result.message);
                }
            } catch (err) {
                this.showToast("Terjadi kesalahan. Coba lagi.");
            }
        });
    },
}));
window.addEventListener("resize", () => {
    Alpine.store("sidebar").open = window.innerWidth >= 1024;
});
window.Alpine = Alpine;
Alpine.start();
