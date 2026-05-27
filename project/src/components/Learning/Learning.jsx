// src/components/Learning/Learning.jsx
import React, { useState, useEffect } from 'react';
import "../../styles/Learning.css";

const steps = [
  {
    number: "01",
    title: "Регистрация",
    description: "Выбираете курс и тариф, получаете доступ к материалам"
  },
  {
    number: "02",
    title: "Обучение",
    description: "Интерактивные видео, практические задания и теория"
  },
  {
    number: "03", 
    title: "Проекты",
    description: "Реальные кейсы, код-ревью от опытных разработчиков"
  },
  {
    number: "04",
    title: "Результат",
    description: "Готовое портфолио и востребованные навыки"
  }
];

export default function Learning() {
  const [currentStep, setCurrentStep] = useState(0);

  useEffect(() => {
    const interval = setInterval(() => {
      setCurrentStep((prev) => (prev + 1) % steps.length);
    }, 4000);
    
    return () => clearInterval(interval);
  }, []);

  return (
    <section className="learning-section">
      <div className="container">
        <div className="section-header">
          <h2 className="section-title">Как проходит обучение</h2>
          <p className="section-subtitle">От регистрации до получения результата - всего 4 простых шага</p>
        </div>
        
        <div className="learning-steps">
          <div className="steps-progress">
            <div 
              className="progress-bar" 
              style={{ width: `${(currentStep / (steps.length - 1)) * 100}%` }}
            ></div>
          </div>
          
          <div className="steps-container">
            {steps.map((step, index) => (
              <div 
                key={index} 
                className={`step-card ${index <= currentStep ? 'active' : ''} ${index === currentStep ? 'current' : ''}`}
                onClick={() => setCurrentStep(index)}
              >
                <div className="step-number">{step.number}</div>
                <div className="step-content">
                  <h3 className="step-title">{step.title}</h3>
                  <p className="step-description">{step.description}</p>
                </div>
                <div className="step-indicator">
                  {index < steps.length - 1 && (
                    <div className="step-connector"></div>
                  )}
                  <div className="step-dot"></div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
}