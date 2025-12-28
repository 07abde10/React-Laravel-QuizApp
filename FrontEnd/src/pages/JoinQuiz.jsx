import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { quizService, authService } from '../services/api';
import './JoinQuiz.css';

export default function JoinQuiz() {
  const [code, setCode] = useState('');
  const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      const res = await quizService.getQuizByCode(code.trim());

      console.log(res)

      console.log('Quiz found:', res.data);
      const quiz = res.data.data;
      navigate(`/quiz/${quiz.id}`);
    } catch (err) {
      console.error('Error finding quiz:', err);
    }
  };

  const handleLogout = async () => {
    try {
      await authService.logout();
      navigate('/login');
    } catch (e) {
      console.error('Logout error:', e);
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
          <input value={code} onChange={(e) => setCode(e.target.value)} placeholder="Enter code quiz" required />
          <button className="btn-primary">Join</button>
        </form>
      </div>
    </div>
  );
}