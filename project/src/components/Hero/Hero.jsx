// src/components/Hero/Hero.jsx
import React from 'react';
import video from "../../assets/video.mp4";
import "../../styles/Hero.css";

const achievements = [
  { title: "10+", text: "лет опыта в IT" },
  { title: "500+", text: "выпускников" },
  { title: "95%", text: "трудоустройство" },
  { title: "50+", text: "реальных проектов" },
  { title: "4.8/5", text: "средний рейтинг курсов" },
  { title: "24/7", text: "поддержка менторов" },
];

export default function Hero() {
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
    <section className="hero-section">
      <video autoPlay muted loop className="hero-video">
        <source src={video} type="video/mp4" />
      </video>

      <div className="hero-container container">
        <div className="hero-left">
          <h1 className="hero-title">
            <strong>Онлайн-обучение</strong><br />IT-профессиям
          </h1>
          <p>Практика, проекты и менторы</p>
          <a 
            href="#pricing" 
            className="hero-button"
            onClick={scrollToPricing}
          >
            Тарифы
          </a>
        </div>

        <div className="hero-right">
          {achievements.map((a, i) => (
            <div key={i} className="achievement-item">
              <div className="achievement-wall"></div>
              <div className="achievement-content">
                <span className="achievement-number">{a.title}</span>
                <span className="achievement-text">{a.text}</span>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}