// src/components/Mentors/Mentors.jsx
import React from 'react';
import "../../styles/Mentors.css";

import mentor1 from "../../assets/avatar3.png";
import mentor2 from "../../assets/avatar1.png";
import mentor3 from "../../assets/avatar2.png";
import mentor4 from "../../assets/avatar4.png";

const mentors = [
  {
    name: "Алексей",
    role: "Senior Frontend Developer",
    exp: "6 лет в IT, 3 года преподавания",
    tech: ["React", "TypeScript", "Next.js"],
    company: "ex-Яндекс",
    bio: "Специализируется на современных фреймворках и архитектуре веб-приложений",
    avatar: mentor1
  },
  {
    name: "Ирина",
    role: "Backend Team Lead",
    exp: "8 лет разработки, 4 года менторства",
    tech: ["Node.js", "Python", "PostgreSQL"],
    company: "Тинькофф",
    bio: "Эксперт по микросервисной архитектуре и высоконагруженным системам",
    avatar: mentor2
  },
  {
    name: "Максим",
    role: "DevOps инженер",
    exp: "5 лет в DevOps, 2 года преподавания",
    tech: ["Docker", "Kubernetes", "AWS"],
    company: "СберТех",
    bio: "Помогает студентам освоить современные практики развертывания и мониторинга",
    avatar: mentor3
  },
  {
    name: "Анна",
    role: "Fullstack Developer",
    exp: "7 лет полного цикла разработки",
    tech: ["Vue.js", "NestJS", "MongoDB"],
    company: "Ozon",
    bio: "Опыт в создании комплексных решений от идеи до продакшена",
    avatar: mentor4
  }
];

export default function Mentors() {
  return (
    <section className="mentors-section" id="mentors">
      <div className="container">
        <div className="section-header">
          <h2 className="section-title">Менторы, которые вас обучат</h2>
          <p className="section-subtitle">
            Практикующие разработчики из ведущих IT-компаний с многолетним опытом
          </p>
        </div>

        <div className="mentors-grid">
          {mentors.map((mentor, index) => (
            <div key={index} className="mentor-card">
              <div className="mentor-avatar-container">
                <div className="mentor-avatar">
                  <img src={mentor.avatar} alt={mentor.name} />
                  <div className="avatar-placeholder">{mentor.name.charAt(0)}</div>
                </div>
                <div className="mentor-company">
                  <span className="company-icon">🏢</span>
                  <span className="company-name">{mentor.company}</span>
                </div>
              </div>
              
              <div className="mentor-info">
                <h3 className="mentor-name">{mentor.name}</h3>
                <p className="mentor-role">{mentor.role}</p>
                <p className="mentor-bio">{mentor.bio}</p>
                
                <div className="mentor-experience">
                  <span className="experience-icon">⏱️</span>
                  <span className="experience-text">{mentor.exp}</span>
                </div>
                
                <div className="mentor-tech">
                  <div className="tech-label">Стек технологий:</div>
                  <div className="tech-tags">
                    {mentor.tech.map((tech, i) => (
                      <span key={i} className="tech-tag">{tech}</span>
                    ))}
                  </div>
                </div>
                
                <div className="mentor-contact">
                  <button className="contact-button">
                    <span className="button-icon">💬</span>
                    <span className="button-text">Задать вопрос</span>
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