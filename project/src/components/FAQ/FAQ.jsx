// src/components/FAQ/FAQ.jsx
import React from 'react';
import { useState } from 'react';
import '../../styles/FAQ.css';

const faqItems = [
  {
    question: "Как проходит обучение?",
    answer: "Обучение полностью онлайн с гибким графиком. Каждую неделю вы получаете новый модуль с видеоуроками, практическими заданиями и материалами для изучения. Регулярно проводятся вебинары с разбором сложных тем, а менторы доступны для консультаций в чате 24/7. Все проекты проходят код-ревью с детальными комментариями."
  },
  {
    question: "Нужен ли опыт программирования?",
    answer: "Для большинства курсов опыт не требуется - мы начинаем с основ. Однако для продвинутых программ рекомендуем базовое понимание алгоритмов. Для абсолютных новичков есть специальные вводные модули, которые помогут освоить фундаментальные концепции программирования."
  },
  {
    question: "Какой график обучения?",
    answer: "Вы можете учиться в удобном для вас темпе. Средняя продолжительность курса - 4-6 месяцев при нагрузке 10-15 часов в неделю. Есть возможность ускоренного прохождения. Все материалы доступны в любое время, а дедлайны по заданиям гибкие."
  },
  {
    question: "Помогаете ли с трудоустройством?",
    answer: "Да, мы предоставляем комплексную поддержку: готовим резюме и портфолио, проводим mock-собеседования, даем доступ к базе вакансий от партнеров и рекомендации для работодателей. 85% наших выпускников находят работу в течение 3 месяцев после завершения курса."
  },
  {
    question: "Какие технологии изучаются?",
    answer: "Мы обучаем только актуальным и востребованным технологиям: современным фреймворкам (React, Vue, Angular), backend-технологиям (Node.js, Python, Go), базам данных (PostgreSQL, MongoDB), DevOps-инструментам (Docker, Kubernetes) и методологиям разработки."
  },
  {
    question: "Есть ли рассрочка или оплата частями?",
    answer: "Да, мы предлагаем гибкие условия оплаты: помесячная оплата, рассрочка на 6-12 месяцев без процентов и возможность оплаты после трудоустройства на некоторых программах. Также действуют скидки при ранней регистрации и групповом обучении."
  },
  {
    question: "Получу ли я сертификат?",
    answer: "После успешного завершения курса вы получите официальный сертификат, подтверждающий приобретенные навыки. Сертификат включает детальную информацию о пройденных темах и выполненном проекте. Он признается IT-компаниями и может быть верифицирован онлайн."
  },
  {
    question: "Можно ли получить возврат средств?",
    answer: "Да, мы предоставляем гарантию возврата в течение 14 дней после начала обучения, если курс не подошел вам по каким-либо причинам. После этого срока возможен возврат пропорционально пройденному материалу. Все условия четко прописаны в договоре."
  }
];

export default function FAQ() {
  const [activeKeys, setActiveKeys] = useState(['']);

  const handlePanelChange = (key) => {
    setActiveKeys(activeKeys.includes(key) ? 
      activeKeys.filter(k => k !== key) : 
      [...activeKeys, key]);
  };

  return (
    <section className="faq-section">
      <div className="container">
        <div className="section-header">
          <h2 className="section-title">Часто задаваемые вопросы</h2>
          <p className="section-subtitle">
            Ответы на самые популярные вопросы об обучении в нашей школе
          </p>
        </div>

        <div className="faq-container">
          {faqItems.map((item, index) => (
            <div 
              key={index}
              className={`faq-item ${activeKeys.includes(String(index)) ? 'active' : ''}`}
            >
              <div 
                className="faq-question"
                onClick={() => handlePanelChange(String(index))}
                role="button"
                tabIndex={0}
                onKeyPress={(e) => e.key === 'Enter' && handlePanelChange(String(index))}
              >
                <div className="question-content">
                  
                  <h3 className="question-text">{item.question}</h3>
                </div>
                <div className="question-toggle">
                  <span className="toggle-icon">
                    {activeKeys.includes(String(index)) ? '−' : '+'}
                  </span>
                </div>
              </div>
              
              <div className="faq-answer">
                <div className="answer-content">
                  <p>{item.answer}</p>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}