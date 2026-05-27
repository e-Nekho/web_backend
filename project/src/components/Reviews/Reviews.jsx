// src/components/Reviews/Reviews.jsx
import React, { useState, useEffect } from 'react';
import "../../styles/Reviews.css";

const reviews = [
  {
    id: 1,
    name: "Александр Петров",
    position: "Frontend Developer в Яндекс",
    text: "Прошел курс Frontend-разработки за 4 месяца. Уже на 3-м месяце получил оффер от Яндекса. Особенно ценю практические задания - они были максимально приближены к реальным задачам.",
    rating: 5,
    date: "15 января 2024"
  },
  {
    id: 2,
    name: "Мария Сидорова",
    position: "Backend Developer в Тинькофф",
    text: "После 6 месяцев обучения сменила профессию с маркетолога на backend-разработчика. Менторы помогали даже в нерабочее время. Сейчас работаю в команде над высоконагруженным проектом.",
    rating: 5,
    date: "3 марта 2024"
  },
  {
    id: 3,
    name: "Дмитрий Иванов",
    position: "Fullstack Developer в Ozon",
    text: "Лучшая инвестиция в себя! Из плюсов: актуальный стек, реальные проекты в портфолио и персональный подход. Из минусов - не нашел.",
    rating: 5,
    date: "28 февраля 2024"
  },
  {
    id: 4,
    name: "Екатерина Козлова",
    position: "DevOps инженер в СберТех",
    text: "Перешел с позиции системного администратора. Курс по DevOps дал именно те навыки, которые требуют работодатели. Особенно полезны были лабораторные работы с Kubernetes.",
    rating: 4,
    date: "10 января 2024"
  },
  {
    id: 5,
    name: "Анна Смирнова",
    position: "React Developer в VK",
    text: "После университета не мог найти работу 6 месяцев. После курса получила 3 предложения о работе за первую неделю поиска. Рекомендую всем начинающим!",
    rating: 5,
    date: "22 февраля 2024"
  },
  {
    id: 6,
    name: "Михаил Орлов",
    position: "Python Developer в Lamoda",
    text: "Прекрасный баланс теории и практики. Каждую неделю - новый проект, каждую тему - разбор с ментором. За 5 месяцев с нуля до junior-разработчика.",
    rating: 5,
    date: "5 марта 2024"
  }
];

export default function Reviews() {
  const [currentIndex, setCurrentIndex] = useState(0);
  const [isAnimating, setIsAnimating] = useState(false);

  const totalReviews = reviews.length;

  const getVisibleReviews = () => {
    const prevIndex = (currentIndex - 1 + totalReviews) % totalReviews;
    const nextIndex = (currentIndex + 1) % totalReviews;
    
    return [
      { ...reviews[prevIndex], position: 'left' },
      { ...reviews[currentIndex], position: 'center' },
      { ...reviews[nextIndex], position: 'right' }
    ];
  };

  const goToNext = () => {
    if (isAnimating) return;
    
    setIsAnimating(true);
    setTimeout(() => {
      setCurrentIndex((prev) => (prev + 1) % totalReviews);
      setIsAnimating(false);
    }, 500);
  };

  const goToPrev = () => {
    if (isAnimating) return;
    
    setIsAnimating(true);
    setTimeout(() => {
      setCurrentIndex((prev) => (prev - 1 + totalReviews) % totalReviews);
      setIsAnimating(false);
    }, 500);
  };

  const goToReview = (index) => {
    if (isAnimating || index === currentIndex) return;
    
    setIsAnimating(true);
    setTimeout(() => {
      setCurrentIndex(index);
      setIsAnimating(false);
    }, 500);
  };

  useEffect(() => {
    const interval = setInterval(() => {
      if (!isAnimating) {
        goToNext();
      }
    }, 5000);
    
    return () => clearInterval(interval);
  }, [isAnimating]);

  const visibleReviews = getVisibleReviews();

  return (
    <section className="reviews-section" id="reviews">
      <div className="container">
        <div className="section-header">
          <h2 className="section-title">Что говорят наши студенты</h2>
          <p className="section-subtitle">
            Реальные истории успеха от выпускников, которые уже работают в IT
          </p>
        </div>

        <div className="reviews-container">
          <button 
            className="review-nav-button prev"
            onClick={goToPrev}
            disabled={isAnimating}
            aria-label="Предыдущий отзыв"
          >
            ←
          </button>
          
          <div className="reviews-track">
            {visibleReviews.map((review) => (
              <div 
                key={review.id}
                className={`review-card ${review.position} ${isAnimating ? 'animating' : ''}`}
                style={{
                  '--start-x': review.position === 'left' ? '-20%' : 
                               review.position === 'right' ? '20%' : '0'
                }}
              >
                <div className="review-header">
                  <div className="review-avatar">
                    <div className="avatar-placeholder">
                      {review.name.split(' ').map(n => n[0]).join('')}
                    </div>
                  </div>
                  <div className="review-author">
                    <h3 className="author-name">{review.name}</h3>
                    <p className="author-position">{review.position}</p>
                    <div className="review-date">{review.date}</div>
                  </div>
                </div>
                
                <div className="review-rating">
                  {[...Array(5)].map((_, i) => (
                    <span 
                      key={i} 
                      className={`star ${i < review.rating ? 'filled' : ''}`}
                    >
                      ★
                    </span>
                  ))}
                </div>
                
                <div className="review-text">
                  <p>{review.text}</p>
                </div>
              </div>
            ))}
          </div>
          
          <button 
            className="review-nav-button next"
            onClick={goToNext}
            disabled={isAnimating}
            aria-label="Следующий отзыв"
          >
            →
          </button>
        </div>

        <div className="reviews-dots">
          {reviews.map((_, index) => (
            <button
              key={index}
              className={`review-dot ${index === currentIndex ? 'active' : ''}`}
              onClick={() => goToReview(index)}
              disabled={isAnimating}
              aria-label={`Перейти к отзыву ${index + 1}`}
            />
          ))}
        </div>
      </div>
    </section>
  );
}