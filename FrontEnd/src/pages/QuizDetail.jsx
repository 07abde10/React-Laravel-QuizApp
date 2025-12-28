import React, { useEffect, useState, useRef } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { quizService, attemptService, responseService, authService } from '../services/api';
import QuestionCard from '../components/QuestionCard';
import './QuizDetail.css';

export default function QuizDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [quiz, setQuiz] = useState(null);
  const [questions, setQuestions] = useState([]);
  const [answers, setAnswers] = useState({});
  // const [loading, setLoading] = useState(true);
  const [attempt, setAttempt] = useState(null);
  const [remaining, setRemaining] = useState(null); // seconds
  const timerRef = useRef(null);

  useEffect(() => {
    const load = async () => {
      try {
        const qRes = await quizService.getQuiz(id);
        const quiz = qRes.data.data;
        setQuiz(quiz);
        
        // Use questions from quiz data
        const qs = quiz.questions || [];
        setQuestions(qs);
      } catch (e) {
        console.error('Failed to load quiz:', e);
      } 
    };

    load();

    return () => { if (timerRef.current) clearInterval(timerRef.current); };
  }, [id]);

  const findStudentId = async () => {
    const stored = localStorage.getItem('user');
    if (stored) {
      const u = JSON.parse(stored);
      if (u.etudiant && u.etudiant.id) return u.etudiant.id;
    }
    try {
      const p = await authService.getProfile();
      return p.data.data.etudiant?.id || null;
    } catch (e) {
      return e;
    }
  };

  console.log(findStudentId);

  const startAttempt = async () => {
    try {
      const payload = { quiz_id: parseInt(id, 10) };
      const res = await attemptService.startAttempt(payload);
      setAttempt(res.data.data);

      // start timer
      const durationMins = quiz?.duree || 0;
      const seconds = Math.max(0, Math.floor(durationMins * 60));
      setRemaining(seconds);
      timerRef.current = setInterval(() => {
        setRemaining((s) => {
          if (s <= 1) {
            clearInterval(timerRef.current);
            handleSubmit();
            return 0;
          }
          return s - 1;
        });
      }, 1000);
    } catch (e) {
      console.error('Failed to start attempt', e);
      alert('Failed to start attempt');
    }
  };

  const handleSelect = (questionId, choixId) => {
    setAnswers((a) => ({ ...a, [questionId]: choixId }));
  };

  const handleSubmit = async () => {
    if (!attempt) {
      alert('Please start the quiz first');
      return;
    }

    const responses = questions.map((q) => ({ question_id: q.id, choix_id: answers[q.id] || null })).filter(r => r.choix_id);
    if (responses.length === 0) {
      if (!confirm('You have not answered any question. Submit anyway?')) return;
    }

    try {
      await responseService.bulkSubmitResponses({ tentative_id: attempt.id, responses });
      await attemptService.finishAttempt(attempt.id);
      navigate(`/results/${attempt.id}`);
    } catch (e) {
      console.error('Submit failed', e);
      alert('Failed to submit responses');
    }
  };

  const formatTime = (secs) => {
    if (secs == null) return '--:--';
    const m = Math.floor(secs / 60).toString().padStart(2, '0');
    const s = (secs % 60).toString().padStart(2, '0');
    return `${m}:${s}`;
  };

  if (!quiz) return <div className="qd-error">Quiz not found</div>;

  return (
    <div className="qd-page">
      <header className="qd-header">
        <div className="qd-title">{quiz.titre}</div>
        <div className="qd-meta">
          <div>{quiz.duree} mins</div>
          <div>{questions.length} questions</div>
          <div className="qd-timer">Time: {formatTime(remaining)}</div>
        </div>
      </header>

      <main className="qd-main">
        {!attempt ? (
          <div className="qd-start">
            <p className="qd-desc">{quiz.description}</p>
            <button className="btn-primary" onClick={startAttempt}>Start Quiz</button>
          </div>
        ) : (
          <>
            <div className="qd-questions">
              {questions.map((q) => (
                <QuestionCard
                  key={q.id}
                  question={q}
                  choices={q.choix_reponses || q.choix || q.choices || []}
                  selected={answers[q.id]}
                  onSelect={handleSelect}
                  disabled={false}
                />
              ))}
            </div>

            <div className="qd-actions">
              <button className="btn-primary" onClick={handleSubmit}>Submit Answers</button>
            </div>
          </>
        )}
      </main>
    </div>
  );
}
