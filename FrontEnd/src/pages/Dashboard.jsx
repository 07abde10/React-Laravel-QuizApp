import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { authService, quizService } from '../services/api';
import './Dashboard.css';

export default function Dashboard() {
  const [user, setUser] = useState(null);
  const [quizzes, setQuizzes] = useState([]);
  const [selectedQuiz, setSelectedQuiz] = useState(null);
  const [analytics, setAnalytics] = useState(null);
  const [loading, setLoading] = useState(true);
  const [analyticsLoading, setAnalyticsLoading] = useState(false);
  const navigate = useNavigate();

  useEffect(() => {
    const fetchData = async () => {
      try {
        // Get quizzes for the first professor in the database
        const quizzesResponse = await quizService.getAllQuizzes({ 
          professeur_id: 1, // Use first professor
          per_page: 50 
        });
        setQuizzes(quizzesResponse.data.data.data || []);
      } catch (error) {
        console.error('Error fetching data:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [navigate]);

  const handleQuizClick = async (quiz) => {
    navigate(`/quiz/${quiz.id}`);
  };

  const handleLogout = async () => {
    try {
      await authService.logout();
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      localStorage.removeItem('user');
      navigate('/login');
    }
  };

  if (loading) {
    return <div className="dashboard-loading">Loading...</div>;
  }

  return (
    <div className="dashboard">
      <header className="dashboard-header">
        <div className="header-content">
          <h1>Quiz App</h1>
          <div className="user-menu">
            <span>Welcome to Quiz App!</span>
            <button onClick={() => navigate('/quizzes/create')} className="btn-create">
              Create Quiz
            </button>
            <button onClick={handleLogout} className="btn-logout">
              Logout
            </button>
          </div>
        </div>
      </header>

      <main className="dashboard-main">
        <div className="dashboard-content">
          <section className="quizzes-section">
            <h2>My Quizzes</h2>
            
            {quizzes.length === 0 ? (
              <p className="no-quizzes">
                No quizzes created yet. Click "Create Quiz" to get started.
              </p>
            ) : (
              <div className="quizzes-grid">
                {quizzes.map((quiz) => (
                  <div 
                    key={quiz.id} 
                    className={`quiz-card ${selectedQuiz?.id === quiz.id ? 'selected' : ''}`}
                    onClick={() => handleQuizClick(quiz)}
                  >
                    <h3>{quiz.titre}</h3>
                    <p>{quiz.description}</p>
                    <div className="quiz-info">
                      <span>⏱️ {quiz.duree} mins</span>
                      <span>📝 {quiz.questions?.length || 0} questions</span>
                      <span>🔑 {quiz.code_quiz}</span>
                    </div>
                    <button className="btn-primary">Start Quiz</button>
                  </div>
                ))}
              </div>
            )}
          </section>

          {user?.role === 'Professeur' && selectedQuiz && (
            <section className="analytics-section">
              <h2>Quiz Analytics - {selectedQuiz.titre}</h2>
              {analyticsLoading ? (
                <div className="analytics-loading">Loading analytics...</div>
              ) : analytics ? (
                <div className="analytics-grid">
                  <div className="stat-card">
                    <div className="stat-number">{analytics.total_attempts}</div>
                    <div className="stat-label">Total Attempts</div>
                  </div>
                  <div className="stat-card success">
                    <div className="stat-number">{analytics.passed_count}</div>
                    <div className="stat-label">Passed</div>
                  </div>
                  <div className="stat-card danger">
                    <div className="stat-number">{analytics.failed_count}</div>
                    <div className="stat-label">Failed</div>
                  </div>
                  <div className="stat-card">
                    <div className="stat-number">{analytics.average_score}%</div>
                    <div className="stat-label">Average Score</div>
                  </div>
                </div>
              ) : (
                <div className="analytics-error">Failed to load analytics</div>
              )}
            </section>
          )}
        </div>
      </main>
    </div>
  );
}
