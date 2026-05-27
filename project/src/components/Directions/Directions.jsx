// src/components/Directions/Directions.jsx
import React from 'react';
import "../../styles/Directions.css";

const items = [
  { title: "Frontend разработка", tech: "React, Vue, TypeScript", duration: "4-6" },
  { title: "Backend разработка", tech: "Node.js, Python, Go", duration: "6-8" },
  { title: "DevOps основы", tech: "Docker, Kubernetes, CI/CD", duration: "3-8" },
  { title: "Интеграция API", tech: "REST, GraphQL, WebSockets", duration: "3-6" },
  { title: "Безопасность", tech: "OAuth, JWT, Хеширование", duration: "2-8" },
  { title: "Оптимизация", tech: "Производительность, Кэширование", duration: "2-4" }
];

export default function Directions() {
  const scrollToPricing = (e) => {
    e.preventDefault();
    const pricingSection = document.getElementById('pricing');
    if (pricingSection) {
      pricingSection.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
      });
    }
  };

  return (
    <section className="directions-section" id="direction">
      <div className="container">
        <div className="section-header">
          <h2 className="section-title">Направления обучения</h2>
          <p className="section-subtitle">Выберите специализацию и освойте востребованные технологии</p>
        </div>
        
        <div className="directions-grid">
          {items.map((item, index) => (
            <div 
              key={index} 
              className={`direction-card ${index % 2 === 0 ? 'card-high' : 'card-low'}`}
              style={{ animationDelay: `${index * 0.1}s` }}
            >
              <div className="direction-content">
                <div className="direction-number">
                  {String(index + 1).padStart(2, '0')}
                </div>
                <h3 className="direction-title">{item.title}</h3>
                <p className="direction-tech">{item.tech}</p>
                <div className="direction-footer">
                  <span className="direction-duration">{item.duration} месяцев</span>
                  <button 
                    className="direction-arrow"
                    onClick={scrollToPricing}
                    aria-label={`Выбрать тариф для ${item.title}`}
                  >
                    →
                  </button>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}