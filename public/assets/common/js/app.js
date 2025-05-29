window.showLoading = function () {
    const el = document.getElementById('globalLoading');
    el.style.display = 'flex';
};

window.hideLoading = function () {
    const el = document.getElementById('globalLoading');
    el.style.display = 'none';
};
