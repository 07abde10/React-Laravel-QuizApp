import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { attemptService } from '../services/api';
import './Results.css';

export default function Results() {
  const { attemptId } = useParams();
  const navigate = useNavigate();
  const [attempt, setAttempt] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const loadResults = async () => {
      try {
        const res = await attemptService.getAttempt(attemptId);
        setAttempt(res.data.data);
      } catch (e) {
        console.error('Failed to load results:', e);
      } finally {
        setLoading(false);
      }
    };

    loadResults();
  }, [attemptId]);

  if (loading) {
    return (
      <div className="results-loading">
        <div className="loading-spinner"></div>
        <p>Loading results...</p>
      </div>
    );
  }

  if (!attempt) {
    return (
      <div className="results-error">
        <div className="error-icon">⚠️</div>
        <h2>Results Not Found</h2>
        <p>We couldn't find the results for this quiz attempt.</p>
        <button className="btn-primary" onClick={() => navigate('/dashboard')}>
          Back to Dashboard
        </button>
      </div>
    );
  }

  const passed = attempt.score >= 50;
  console.log(attempt.score);

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
            <span className="status-icon">{passed ? '✓' : '✗'}</span>
            <span className="status-text">{passed ? 'PASSED' : 'FAILED'}</span>
          </div>

          <div className="results-details">
            <div className="detail-item">
              <span className="detail-label">Date</span>
              <span className="detail-value">
                {attempt.date_passage ? new Date(attempt.date_passage).toLocaleDateString() : 'N/A'}
              </span>
            </div>
            {attempt.duree_passage && (
              <div className="detail-item">
                <span className="detail-label">Time Taken</span>
                <span className="detail-value">{attempt.duree_passage} mins</span>
              </div>
            )}
          </div>
        </div>

        <div className="results-actions">
          <button className="btn-primary" onClick={() => navigate('/dashboard')}>
            Explore Quizzes
          </button>
          <button className="btn-secondary" onClick={() => navigate('/join')}>
            Take Another Quiz
          </button>
        </div>
      </div>
    </div>
  );
}