const html = document.getElementById('htmlPage');
const checkbox = document.getElementById('checkbox');
let toggleCount = 0;

document.addEventListener('DOMContentLoaded', () => {
    const theme = localStorage.getItem('theme');
    if (theme) {
        html.setAttribute("data-bs-theme", theme);
        checkbox.checked = theme === 'dark';
    }
    toggleCount = parseInt(localStorage.getItem('toggleCount')) || 0;
});

checkbox.addEventListener('change', () => {
    if (checkbox.checked) {
        html.setAttribute("data-bs-theme", "dark");
        localStorage.setItem('theme', 'dark');
        console.log('Dark mode enabled');
    } else {
        html.setAttribute("data-bs-theme", "light");
        localStorage.setItem('theme', 'light');
        console.log('Dark mode disabled');
    }

    toggleCount++;
    localStorage.setItem('toggleCount', toggleCount);

    if (toggleCount === 10) {
        window.open('https://youtu.be/nHXnyNGXgA4?t=3', '_blank');
        alert("easter egg gia opoion paizei polu me to dark mode ;) a re vasilaki karra");
        console.log('TWRA? TI GINETAI TWRA?????');
        
        toggleCount = 0;
        localStorage.setItem('toggleCount', toggleCount);
    }
});
