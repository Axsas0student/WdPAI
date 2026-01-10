(() => {
    const STORAGE_KEY = "historiq_theme";

    function systemPrefersDark() {
        return window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches;
    }

    function getTheme() {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved === "light" || saved === "dark") return saved;
        return systemPrefersDark() ? "dark" : "light";
    }

    function setTheme(theme) {
        document.documentElement.setAttribute("data-theme", theme);
        localStorage.setItem(STORAGE_KEY, theme);
    }

    function toggleTheme() {
        const current = document.documentElement.getAttribute("data-theme") || "light";
        setTheme(current === "dark" ? "light" : "dark");
    }

    setTheme(getTheme());

    document.addEventListener("click", (e) => {
        const el = e.target.closest("[data-theme-toggle]");
        if (!el) return;
        e.preventDefault();
        toggleTheme();
    });
})();
