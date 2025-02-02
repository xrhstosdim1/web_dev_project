const html = document.getElementById('htmlPage');
const checkbox = document.getElementById('checkbox');

document.addEventListener('DOMContentLoaded', () => {
    const theme = localStorage.getItem('theme');
    if (theme) {
        html.setAttribute("data-bs-theme", theme);
        checkbox.checked = theme === 'dark';
    }
});

checkbox.addEventListener('change', () => {
    if (checkbox.checked) {
        html.setAttribute("data-bs-theme", "dark");
        localStorage.setItem('theme', 'dark');
    } else {
        html.setAttribute("data-bs-theme", "light");
        localStorage.setItem('theme', 'light');
    }
});
