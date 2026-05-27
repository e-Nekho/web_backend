// src/components/Tasks/Tasks.jsx
import React from 'react';
import "../../styles/Tasks.css";

const tasks = [
  {
    title: "Форма авторизации",
    desc: "Реализуйте безопасную систему входа с двухфакторной аутентификацией и валидацией в реальном времени.",
    complexity: "Начальный",
    tech: ["React", "JWT", "Formik"]
  },
  {
    title: "REST API клиент",
    desc: "Создайте полноценное приложение для работы с внешним API: получение, фильтрация и отображение данных.",
    complexity: "Средний",
    tech: ["Node.js", "Express", "MongoDB"]
  },
  {
    title: "Оптимизация веб-приложения",
    desc: "Проанализируйте и улучшите производительность реального проекта: кэширование, ленивая загрузка, мемоизация.",
    complexity: "Продвинутый",
    tech: ["React", "Webpack", "Lighthouse"]
  },
  {
    title: "Интерактивная карта",
    desc: "Разработайте карту с маркерами, маршрутами и поиском мест, используя современные веб-технологии.",
    complexity: "Средний",
    tech: ["JavaScript", "Leaflet", "API"]
  },
  {
    title: "Чат в реальном времени",
    desc: "Постройте мессенджер с комнатами, уведомлениями и шифрованием сообщений end-to-end.",
    complexity: "Продвинутый",
    tech: ["Socket.io", "React", "Node.js"]
  },
  {
    title: "E-commerce платформа",
    desc: "Создайте интернет-магазин с корзиной, оплатой и личным кабинетом пользователя.",
    complexity: "Средний",
    tech: ["Next.js", "Stripe", "Redux"]
  },
  {
    title: "Панель аналитики",
    desc: "Разработайте dashboard с графиками, таблицами и фильтрами для анализа больших объемов данных.",
    complexity: "Продвинутый",
    tech: ["D3.js", "Chart.js", "PostgreSQL"]
  },
  {
    title: "Мобильное приложение",
    desc: "Создайте кроссплатформенное приложение с нативным UX и офлайн-режимом работы.",
    complexity: "Средний",
    tech: ["React Native", "Expo", "AsyncStorage"]
  },
  {
    title: "Микросервисная архитектура",
    desc: "Спроектируйте и реализуйте систему из независимых микросервисов с API-шлюзом и балансировщиком нагрузки.",
    complexity: "Продвинутый",
    tech: ["Docker", "Kubernetes", "gRPC"]
  }
];

export default function Tasks() {
  return (
    <section className="tasks-section" id="tasks">
      <div className="container">
        <div className="section-header">
          <h2 className="section-title">Реальные проекты, которые вы создадите</h2>
          <p className="section-subtitle">Наши задания - это не учебные примеры, а реальные задачи из IT-индустрии</p>
        </div>

        <div className="tasks-grid">
          {tasks.map((task, index) => (
            <div 
              key={index} 
              className={`task-card complexity-${task.complexity.toLowerCase()}`}
              style={{ animationDelay: `${index * 0.1}s` }}
            >
              <div className="task-header">
                <div className="task-meta">
                  <span className="complexity-badge">{task.complexity}</span>
                  <div className="task-tech">
                    {task.tech.map((tech, i) => (
                      <span key={i} className="tech-tag">{tech}</span>
                    ))}
                  </div>
                </div>
                <h3 className="task-title">{task.title}</h3>
              </div>
              
              <div className="task-content">
                <p className="task-description">{task.desc}</p>
              </div>
              
              <div className="task-footer">
                <button className="task-button">
                  <span className="button-text">Подробнее о задании</span>
                  <span className="button-arrow">→</span>
                </button>
              </div>
              
              <div className="task-decoration">
                <div className="decoration-dot"></div>
                <div className="decoration-line"></div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}