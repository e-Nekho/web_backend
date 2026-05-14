document.addEventListener('DOMContentLoaded', () => {
    const openBtn = document.querySelector('.but');
    const popup = document.querySelector('.form-back');
    const form = document.querySelector('.form');

    // Открытие попапа
    if (openBtn) {
        openBtn.addEventListener('click', () => {
            popup.classList.remove('hidden');
        });
    }

    // Закрытие при клике на темный фон (вокруг формы)
    if (popup) {
        popup.addEventListener('click', (e) => {
            if (e.target === popup) {
                popup.classList.add('hidden');
            }
        });
    }

    // Анимация кнопки при отправке
    if (form) {
        form.addEventListener('submit', () => {
            const submitBtn = form.querySelector('.form-but');
            if (submitBtn) {
                submitBtn.value = 'Сохранение...';
                // Мы не блокируем кнопку (disabled), чтобы PHP мог получить данные,
                // но визуально меняем текст.
            }
        });
    }
});