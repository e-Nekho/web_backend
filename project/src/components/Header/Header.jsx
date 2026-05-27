// src/components/Header/Header.jsx
import React, { useState, useEffect } from 'react';
import logo from "../../assets/logo2.png";
import "../../styles/Header.css";

export default function Header() {
  const [isVisible, setIsVisible] = useState(true);
  const [lastScrollY, setLastScrollY] = useState(0);
  const [isRu, setIsRu] = useState(true);

  // Состояния для авторизации
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const [showModal, setShowModal] = useState(false);
  const [login, setLogin] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');

  const toggleLanguage = () => {
    setIsRu(!isRu);
  };

  const scrollToSection = (sectionId) => (e) => {
    e.preventDefault();
    const section = document.getElementById(sectionId);
    if (section) {
      section.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
      });
    }
  };

  // Проверяем статус авторизации при загрузке страницы
  useEffect(() => {
    fetch('/tasks/project/api.php?action=check_auth')
      .then(res => res.json())
      .then(data => {
        if (data.is_logged_in) {
          setIsLoggedIn(true);
        }
      })
      .catch(err => console.error("Ошибка проверки авторизации:", err));
  }, []);

  // Скролл хедера
  useEffect(() => {
    const controlHeader = () => {
      const currentScrollY = window.scrollY;
      if (currentScrollY > lastScrollY && currentScrollY > 100) {
        setIsVisible(false);
      } else if (currentScrollY < lastScrollY) {
        setIsVisible(true);
      }
      setLastScrollY(currentScrollY);
    };

    window.addEventListener('scroll', controlHeader);
    return () => window.removeEventListener('scroll', controlHeader);
  }, [lastScrollY]);

  // Хэндлер отправки формы логина
  const handleLoginSubmit = (e) => {
    e.preventDefault();
    setError('');

    fetch('/tasks/project/api.php?action=login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ login, password })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        setShowModal(false);
        // Перезагружаем страницу, чтобы обновить сессию во всех компонентах
        window.location.reload();
      } else {
        setError(data.error || 'Ошибка входа');
      }
    })
    .catch(() => setError('Не удалось связаться с сервером.'));
  };

  // Хэндлер выхода
  const handleLogout = () => {
    fetch('/tasks/project/api.php?action=logout', { method: 'POST' })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          window.location.reload();
        }
      });
  };

  return (
    <>
      <header className={`header ${isVisible ? 'visible' : 'hidden'}`}>
        <div className="header-container">
          {/* Логотип */}
          <div className="logo-container">
            <img src={logo} alt="IT Academy Logo" className="header-logo" />
          </div>

          {/* Навигация */}
          <nav className="nav-menu">
            <ul className="nav-list">
              <li className="nav-item"><a href="#direction" className="nav-link" onClick={scrollToSection('direction')}>Направления обучения</a></li>
              <li className="nav-item"><a href="#pricing" className="nav-link" onClick={scrollToSection('pricing')}>Тарифы</a></li>
              <li className="nav-item"><a href="#tasks" className="nav-link" onClick={scrollToSection('tasks')}>Примеры задач</a></li>
              <li className="nav-item"><a href="#reviews" className="nav-link" onClick={scrollToSection('reviews')}>Отзывы</a></li>
              <li className="nav-item"><a href="#form" className="nav-link" onClick={scrollToSection('form')}>Контакты</a></li>
            </ul>
          </nav>

          {/* Правая часть: телефон, язык и кнопка Войти */}
          <div className="header-right">
            <div className="phone-container">
              <a href="tel:+79991234567" className="phone-link">+7 (999) 123-45-67</a>
            </div>
            
            <div className="language-switcher" onClick={toggleLanguage}>
              <span className={`language-option ${isRu ? 'active' : ''}`}>RU</span>
              <span className="language-divider">/</span>
              <span className={`language-option ${!isRu ? 'active' : ''}`}>EN</span>
            </div>

            {/* Динамическая кнопка Войти / Выйти */}
            <div className="auth-block">
              {isLoggedIn ? (
                <button onClick={handleLogout} className="auth-btn logout">Выйти</button>
              ) : (
                <button onClick={() => setShowModal(true)} className="auth-btn login">Войти</button>
              )}
            </div>
          </div>
        </div>
      </header>

      {/* Модальное окно авторизации */}
      {showModal && (
        <div className="modal-overlay" onClick={() => setShowModal(false)}>
          <div className="modal-content" onClick={(e) => e.stopPropagation()}>
            <button className="modal-close" onClick={() => setShowModal(false)}>&times;</button>
            <h3>Вход в систему</h3>
            <p className="modal-subtitle">Используйте логин и пароль, выданные при регистрации</p>
            
            {error && <div className="modal-error">{error}</div>}
            
            <form onSubmit={handleLoginSubmit}>
              <div className="modal-form-group">
                <label>Логин</label>
                <input 
                  type="text" 
                  value={login} 
                  onChange={(e) => setLogin(e.target.value)} 
                  required 
                />
              </div>
              <div className="modal-form-group">
                <label>Пароль</label>
                <input 
                  type="password" 
                  value={password} 
                  onChange={(e) => setPassword(e.target.value)} 
                  required 
                />
              </div>
              <button type="submit" className="modal-submit-btn">Войти</button>
            </form>
          </div>
        </div>
      )}
    </>
  );
}