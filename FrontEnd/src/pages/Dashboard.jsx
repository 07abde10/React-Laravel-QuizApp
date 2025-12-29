import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { CiClock2 } from "react-icons/ci";
import { FaQuestionCircle } from "react-icons/fa";
import { FaCheck } from "react-icons/fa";
import { FaKey } from "react-icons/fa";
import { FaXmark } from "react-icons/fa6";
import "./Dashboard.css";

export default function Dashboard() {
  console.log("Dashboard component loaded");
  const [quizzes, setQuizzes] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const navigate = useNavigate();

  useEffect(() => {
    const fetchData = async () => {
      try {
        console.log("Fetching quizzes...");
        const response = await fetch(
          "http://localhost:8000/api/quizzes?professeur_id=1"
        );
        console.log("Response status:", response.status);

        if (response.ok) {
          const result = await response.json();
          console.log("API result:", result);
          // Handle nested data structure
          const quizzesData = result.data?.data || result.data || [];
          setQuizzes(Array.isArray(quizzesData) ? quizzesData : []);
        } else {
          setError("Failed to fetch quizzes");
        }
      } catch (error) {
        console.error("Fetch error:", error);
        setError("Network error");
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, []);

  const handleLogout = () => {
    localStorage.removeItem("user");
    navigate("/login");
  };

  if (loading) {
    return <div style={{ padding: "20px", fontSize: "18px" }}>Loading...</div>;
  }

  if (error) {
    return (
      <div style={{ padding: "20px", fontSize: "18px", color: "red" }}>
        Error: {error}
      </div>
    );
  }

  return (
    <div className="dashboard">
      <header className="dashboard-header">
        <div className="header-content">
          <h1>Quiz App</h1>
          <div className="user-menu">
            <span>Welcome to Quiz App!</span>
            <button
              onClick={() => navigate("/quizzes/create")}
              className="btn-create"
            >
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
                  <div key={quiz.id} className="quiz-card">
                    <h3>{quiz.titre}</h3>
                    <p>{quiz.description}</p>
                    <div className="quiz-info">
                      <span>
                        <CiClock2 />
                        {quiz.duree} mins
                      </span>
                      <span>
                        <FaQuestionCircle /> Questions
                      </span>
                      <span>
                        <FaKey />
                        {quiz.code_quiz}
                      </span>
                      <span
                        className={`status ${
                          quiz.actif ? "active" : "inactive"
                        }`}
                      >
                        {quiz.actif ? (
                          <>
                            <FaCheck /> Active
                          </>
                        ) : (
                          <><FaXmark /> Inactive</>
                        )}
                      </span>
                    </div>
                    <button
                      className="btn-primary"
                      onClick={() => navigate(`/quiz/${quiz.id}`)}
                    >
                      View Quiz
                    </button>
                  </div>
                ))}
              </div>
            )}
          </section>
        </div>
      </main>
    </div>
  );
}
