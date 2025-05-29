import './bootstrap';

function showLoading() {
    const loader = document.getElementById('globalLoading');
    if (loader) loader.style.display = 'flex';
}

function hideLoading() {
    const loader = document.getElementById('globalLoading');
    if (loader) loader.style.display = 'none';
}