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

    function debounce(fn, ms) {
        let t = null;
        return (...args) => {
            clearTimeout(t);
            t = setTimeout(() => fn(...args), ms);
        };
    }

    document.addEventListener("DOMContentLoaded", () => {
        const input = document.querySelector("[data-topic-search]");
        const list = document.getElementById("topicList");
        if (!input || !list) return;

        const render = (topics) => {
            list.innerHTML = "";
            if (!topics || topics.length === 0) {
                const li = document.createElement("li");
                li.textContent = "Brak wyników";
                list.appendChild(li);
                return;
            }

            for (const t of topics) {
                const li = document.createElement("li");
                li.textContent = `#${t.id} - ${t.name} (sort: ${t.sort_order})`;
                list.appendChild(li);
            }
        };

        const load = debounce(async () => {
            const q = input.value.trim();
            try {
                const res = await fetch(`/admin-topics-search?q=${encodeURIComponent(q)}`, {
                    headers: { "Accept": "application/json" }
                });
                if (!res.ok) return;
                const data = await res.json();
                if (Array.isArray(data)) render(data);
            } catch (_) {
            }
        }, 200);

        input.addEventListener("input", load);
    });
})();
