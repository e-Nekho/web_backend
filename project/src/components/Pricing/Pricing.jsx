// src/components/Pricing/Pricing.jsx
import React, { useState, useEffect, useRef } from 'react';
import "../../styles/Pricing.css";

const tariffs = [
  {
    title: "Стартовый",
    price: "0 ₽",
    yearPrice: "0 ₽",
    period: "бесплатно",
    features: [
      "3 вводных урока",
      "Доступ к комьюнити",
      "Пробный тест знаний",
      "Базовые шаблоны проектов"
    ],
    bonus: "Попробуйте перед покупкой",
    color: "gray",
    popular: false,
    discount: 0
  },
  {
    title: "Базовый",
    price: "990 ₽",
    yearPrice: "11 286 ₽",
    period: "в месяц",
    features: [
      "Доступ к 5 курсам",
      "Практические задания",
      "Проверка 3 проектов",
      "Фидбек от куратора",
      "Сертификат о прохождении"
    ],
    bonus: "Самый популярный выбор",
    color: "blue",
    popular: true,
    discount: 5
  },
  {
    title: "Расширенный",
    price: "1 990 ₽",
    yearPrice: "21 223 ₽",
    period: "в месяц",
    features: [
      "Все курсы платформы",
      "Неограниченные проекты",
      "Персональный план обучения",
      "Приоритетная поддержка 24/7",
      "Работа с ментором раз в неделю"
    ],
    bonus: "Рекомендуем для карьерного роста",
    color: "purple",
    popular: false,
    discount: 11
  },
  {
    title: "VIP",
    price: "3 590 ₽",
    yearPrice: "36 187 ₽",
    period: "в месяц",
    features: [
      "Индивидуальная программа",
      "Ежедневные созвоны с ментором",
      "Гарантия трудоустройства",
      "Подготовка резюме и портфолио",
      "Сопровождение на собеседованиях",
      "Пожизненный доступ к материалам"
    ],
    bonus: "Максимальный результат",
    color: "gold",
    popular: false,
    discount: 16
  }
];

export default function Pricing() {
  const [currentIndex, setCurrentIndex] = useState(0);
  const [slidesPerView, setSlidesPerView] = useState(3);
  const [isAnimating, setIsAnimating] = useState(false);
  const carouselRef = useRef(null);

  const scrollToForm = (e) => {
    e.preventDefault();
    const formSection = document.getElementById('form');
    if (formSection) {
      formSection.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
      });
    }
  };

  const nextSlide = () => {
    if (isAnimating) return;
    
    setIsAnimating(true);
    setCurrentIndex((prevIndex) => {
      const maxIndex = tariffs.length - slidesPerView;
      return prevIndex >= maxIndex ? 0 : prevIndex + 1;
    });
    
    setTimeout(() => setIsAnimating(false), 500);
  };

  const prevSlide = () => {
    if (isAnimating) return;
    
    setIsAnimating(true);
    setCurrentIndex((prevIndex) => {
      return prevIndex <= 0 ? tariffs.length - slidesPerView : prevIndex - 1;
    });
    
    setTimeout(() => setIsAnimating(false), 500);
  };

  const goToSlide = (index) => {
    if (isAnimating || index === currentIndex) return;
    
    setIsAnimating(true);
    setCurrentIndex(index);
    setTimeout(() => setIsAnimating(false), 500);
  };

  useEffect(() => {
    const updateSlidesPerView = () => {
      const width = window.innerWidth;
      if (width >= 1200) {
        setSlidesPerView(3);
      } else if (width >= 768) {
        setSlidesPerView(2);
      } else {
        setSlidesPerView(1);
      }
    };

    updateSlidesPerView();
    window.addEventListener('resize', updateSlidesPerView);
    
    return () => window.removeEventListener('resize', updateSlidesPerView);
  }, []);

  const maxIndex = tariffs.length - slidesPerView;

  return (
    <section className="pricing-section" id="pricing">
      <div className="container">
        <div className="section-header">
          <h2 className="section-title">Выберите свой тариф</h2>
          <p className="section-subtitle">
            Инвестируйте в свое образование с умом - каждая ступень приближает вас к цели
          </p>
        </div>

        <div className="carousel-container">
          <button 
            className="carousel-button prev"
            onClick={prevSlide}
            disabled={isAnimating}
            aria-label="Предыдущий слайд"
          >
            ←
          </button>
          
          <div className="carousel-wrapper" ref={carouselRef}>
            <div 
              className="carousel-track"
              style={{
                transform: `translateX(calc(-${currentIndex * (100 / slidesPerView)}% - ${currentIndex * 30}px))`,
                transition: isAnimating ? 'transform 0.5s ease' : 'none'
              }}
            >
              {tariffs.map((tariff, index) => (
                <div 
                  key={index}
                  className={`pricing-card ${tariff.color} ${tariff.popular ? 'popular' : ''}`}
                  style={{
                    flex: `0 0 calc(${100 / slidesPerView}% - ${30 * (slidesPerView - 1) / slidesPerView}px)`,
                    marginRight: '30px'
                  }}
                >
                  {tariff.popular && (
                    <div className="popular-badge">Самый популярный</div>
                  )}
                  
                  <div className="card-header">
                    <h3 className="card-title">{tariff.title}</h3>
                    <div className="price-section">
                      <div className="price-main">{tariff.price}</div>
                      <div className="price-period">{tariff.period}</div>
                      
                      {tariff.discount > 0 && (
                        <div className="year-pricing">
                          <div className="year-price">
                            <span className="year-price-value">{tariff.yearPrice}</span>
                            <span className="year-price-period">в год</span>
                          </div>
                          <div className="discount-badge">
                            <span className="discount-percent">-{tariff.discount}%</span>
                            <span className="discount-text">скидка</span>
                          </div>
                        </div>
                      )}
                    </div>
                    <div className="bonus-text">{tariff.bonus}</div>
                  </div>

                  <div className="card-content">
                    <div className="features-list">
                      {tariff.features.map((feature, idx) => (
                        <div key={idx} className="feature-item">
                          <span className="feature-icon">✓</span>
                          <span className="feature-text">{feature}</span>
                        </div>
                      ))}
                    </div>
                  </div>

                  <div className="card-footer">
                    <button className="select-button">
                      {tariff.title === "Стартовый" ? "Начать бесплатно" : "Выбрать тариф"}
                    </button>
                    {tariff.title !== "Стартовый" && (
                      <div className="saving-info">
                        <span className="saving-icon">💎</span>
                        <span className="saving-text">Экономия {tariff.discount}% за год</span>
                      </div>
                    )}
                  </div>
                </div>
              ))}
            </div>
          </div>

          <button 
            className="carousel-button next"
            onClick={nextSlide}
            disabled={isAnimating}
            aria-label="Следующий слайд"
          >
            →
          </button>
        </div>

        <div className="carousel-dots">
          {Array.from({ length: maxIndex + 1 }).map((_, index) => (
            <button
              key={index}
              className={`carousel-dot ${index === currentIndex ? 'active' : ''}`}
              onClick={() => goToSlide(index)}
              disabled={isAnimating}
              aria-label={`Перейти к слайду ${index + 1}`}
            />
          ))}
        </div>

        <div className="custom-offer">
          <div className="offer-icon">💡</div>
          <h3 className="offer-title">Вам не подходят наши тарифы?</h3>
          <p className="offer-description">
            Оставьте заявку и мы предложим вам индивидуальные условия! 
            <br />Получите расчет персонального тарифа под ваши задачи.
          </p>
          <button 
            className="custom-button"
            onClick={scrollToForm}
          >
            Получить индивидуальный тариф
          </button>
        </div>
      </div>
    </section>
  );
}