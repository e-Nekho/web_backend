// src/components/Header/Header.jsx
import React, { useState, useEffect } from 'react';
import logo from "../../assets/logo2.png";
import "../../styles/Header.css";

export default function Header() {
  const [isVisible, setIsVisible] = useState(true);
  const [lastScrollY, setLastScrollY] = useState(0);
  const [isRu, setIsRu] = useState(true);

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

  useEffect(() => {
    const controlHeader = () => {
      const currentScrollY = window.scrollY;
      
      if (currentScrollY > lastScrollY && currentScrollY > 100) {
        // Скролл вниз
        setIsVisible(false);
      } else if (currentScrollY < lastScrollY) {
        // Скролл вверх
        setIsVisible(true);
      }
      
      setLastScrollY(currentScrollY);
    };

    window.addEventListener('scroll', controlHeader);
    
    return () => {
      window.removeEventListener('scroll', controlHeader);
    };
  }, [lastScrollY]);

  return (
    <header className={`header ${isVisible ? 'visible' : 'hidden'}`}>
      <div className="header-container">
        {/* Логотип */}
        <div className="logo-container">
          <img src={logo} alt="IT Academy Logo" className="header-logo" />
        </div>

        {/* Навигация */}
        <nav className="nav-menu">
          <ul className="nav-list">
            <li className="nav-item">
              <a 
                href="#direction" 
                className="nav-link" 
                onClick={scrollToSection('direction')}
              >
                Направления обучения
              </a>
            </li>
            <li className="nav-item">
              <a 
                href="#pricing" 
                className="nav-link" 
                onClick={scrollToSection('pricing')}
              >
                Тарифы
              </a>
            </li>
            <li className="nav-item">
              <a 
                href="#tasks" 
                className="nav-link" 
                onClick={scrollToSection('tasks')}
              >
                Примеры задач
              </a>
            </li>
            <li className="nav-item">
              <a 
                href="#reviews" 
                className="nav-link" 
                onClick={scrollToSection('reviews')}
              >
                Отзывы
              </a>
            </li>
            <li className="nav-item">
              <a 
                href="#form" 
                className="nav-link" 
                onClick={scrollToSection('form')}
              >
                Контакты
              </a>
            </li>
          </ul>
        </nav>

        {/* Правая часть: телефон и переключатель языка */}
        <div className="header-right">
          <div className="phone-container">
            <a href="tel:+79991234567" className="phone-link">
              +7 (999) 123-45-67
            </a>
          </div>
          
          <div className="language-switcher" onClick={toggleLanguage}>
            <span className={`language-option ${isRu ? 'active' : ''}`}>RU</span>
            <span className="language-divider">/</span>
            <span className={`language-option ${!isRu ? 'active' : ''}`}>EN</span>
          </div>
        </div>
      </div>
    </header>
  );
}