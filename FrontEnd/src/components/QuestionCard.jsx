import React from 'react';
import './QuestionCard.css';

export default function QuestionCard({ question, choices = [], selected, onSelect, disabled = false }) {
  return (
    <div className="qc-card">
      <div className="qc-header">
        <div className="qc-title">{question.enonce || question.intitule || question.texte || `Question ${question.id}`}</div>
        <div className="qc-points">{question.points ?? 0} pts</div>
      </div>

      <div className="qc-choices">
        {choices.map((c) => (
          <label key={c.id} className={`qc-choice ${selected === c.id ? 'selected' : ''} ${disabled ? 'disabled' : ''}`}>
            <input
              type="radio"
              name={`q_${question.id}`}
              value={c.id}
              checked={selected === c.id}
              disabled={disabled}
              onChange={() => onSelect(question.id, c.id)}
            />
            <span className="qc-choice-text">{c.texte_choix || c.libelle || c.texte || c.label}</span>
          </label>
        ))}
      </div>
    </div>
  );
}
