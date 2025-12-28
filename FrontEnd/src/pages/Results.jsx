import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { attemptService } from '../services/api';
import './Results.css';

export default function Results() {
  const { attemptId } = useParams();
  const navigate = useNavigate();
  const [attempt, setAttempt] = useState(null);
  useEffect(() => {
    const loadResults = async () => {
      try {
        const res = await attemptService.getAttempt(attemptId);
        setAttempt(res.data.data);
      } catch (e) {
        console.error('Failed to load results:', e);
      }
    };

    loadResults();
  }, [attemptId]);

  if (!attempt) {
    return (
      <div className="results-error">
        <h2>Results Not Found</h2>
        <button className="btn-primary" onClick={() => navigate('/join')}>
          Back
        </button>
      </div>
    );
  }

  const passed = attempt.score >= 50;

  return (
    <div className="results-page">
      <div className="results-card">
        <div className="results-header">
          <h1>Quiz Results</h1>
          <p className="results-subtitle">Your performance summary</p>
        </div>

        <div className="results-content">
          <div className="score-circle">
            <svg className="score-ring" viewBox="0 0 120 120">
              <circle
                className="score-ring-background"
                cx="60"
                cy="60"
                r="54"
                strokeWidth="8"
              />
              <circle
                className="score-ring-progress"
                cx="60"
                cy="60"
                r="54"
                strokeWidth="8"
                strokeDasharray={`${(attempt.score || 0) * 3.39} 339`}
                style={{
                  stroke: passed ? '#27ae60' : '#e74c3c'
                }}
              />
            </svg>
            <div className="score-text">
              <div className="score-value">{attempt.score || 0}%</div>
              <div className="score-label">Score</div>
            </div>
          </div>

          <div className={`status-badge ${passed ? 'passed' : 'failed'}`}>
            <span className="status-text">{passed ? 'PASSED' : 'FAILED'}</span>
          </div>
        </div>
        <div className="results-actions">
          <button className="btn-secondary" onClick={() => navigate('/join')}>
            Take Another Quiz
          </button>
        </div>
      </div>
    </div>
  );
}