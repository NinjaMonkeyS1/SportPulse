// Функцията, която сменя атрибута на body и иконата на бутона
function applyTheme(theme) {
    document.body.setAttribute('data-theme', theme);
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.textContent = theme === 'dark' ? '☀️' : '🌙';
    }
}

// Извикваме я ВЕДНАГА, за да няма премигване при зареждане на нов таб
const savedTheme = localStorage.getItem('sp_theme') || 'light';
applyTheme(savedTheme);

// Изчакваме да се зареди HTML-а, за да вържем клика на бутона
document.addEventListener('DOMContentLoaded', () => {
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        // Актуализираме иконата още веднъж, за сигурност
        themeToggle.textContent = savedTheme === 'dark' ? '☀️' : '🌙';
        
        themeToggle.addEventListener('click', () => {
            const current = document.body.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            applyTheme(current);
            localStorage.setItem('sp_theme', current); // Това го записва за целия сайт!
        });
    }
});