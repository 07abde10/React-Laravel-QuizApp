import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { quizService, authService } from '../services/api';
import './JoinQuiz.css';

export default function JoinQuiz() {
  const [code, setCode] = useState('');
  const [error, setError] = useState('');
  const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    console.log('Searching for quiz with code:', code.trim());
    try {
      const res = await quizService.getQuizByCode(code.trim());
      console.log('Quiz found:', res.data);
      const quiz = res.data.data;
      navigate(`/quiz/${quiz.id}`);
    } catch (err) {
      console.error('Error finding quiz:', err);
      setError(err.response?.data?.message || 'Quiz not found');
    }
  };

  const handleLogout = async () => {
    try {
      await authService.logout();
    } catch (e) {
      console.error('Logout error:', e);
    } finally {
      localStorage.removeItem('authToken');
      localStorage.removeItem('user');
      navigate('/login');
    }
  };

  return (
    <div className="join-page">
      <div className="join-card">
        <div className="join-header">
          <h2>Enter Quiz Code</h2>
          <button onClick={handleLogout} className="btn-logout">
            Logout
          </button>
        </div>
        <form onSubmit={handleSubmit} className="join-form">
          <input value={code} onChange={(e) => setCode(e.target.value)} placeholder="Enter code (e.g. QUIZ-ABC123)" required />
          <button className="btn-primary">Join</button>
        </form>
        {error && <div className="join-error">{error}</div>}
      </div>
    </div>
  );
}