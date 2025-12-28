import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { useMemo } from 'react';
import Login from './pages/Login';
import Register from './pages/Register';
import Dashboard from './pages/Dashboard';
import AdminDashboard from './pages/AdminDashboard';
import QuizDetail from './pages/QuizDetail';
import QuizCreate from './pages/QuizCreate';
import JoinQuiz from './pages/JoinQuiz';
import Results from './pages/Results';
import './App.css';

function PrivateRoute({ children }) {
  const token = localStorage.getItem('authToken');
  return token ? children : <Navigate to="/login" />;
}

function App() {
  const token = localStorage.getItem('authToken');
  const user = useMemo(() => {
    const stored = localStorage.getItem('user');
    return stored ? JSON.parse(stored) : null;
  }, []);
  
  // Determine default route based on user role
  const defaultRoute = useMemo(() => {
    if (!token) return '/login';
    if (user && user.role === 'Administrateur') return '/admin';
    if (user && user.role === 'Etudiant') return '/join';
    return '/dashboard';
  }, [token, user]);

  return (
    <Router>
      <Routes>
        <Route path="/login" element={token ? <Navigate to={defaultRoute} /> : <Login />} />
        <Route path="/register" element={token ? <Navigate to={defaultRoute} /> : <Register />} />
        <Route
          path="/dashboard"
          element={
            <PrivateRoute>
              <Dashboard />
            </PrivateRoute>
          }
        />
        <Route
          path="/admin"
          element={
            <PrivateRoute>
              <AdminDashboard />
            </PrivateRoute>
          }
        />
        <Route
          path="/quiz/:id"
          element={
            <PrivateRoute>
              <QuizDetail />
            </PrivateRoute>
          }
        />
        <Route
          path="/quizzes/create"
          element={
            <PrivateRoute>
              <QuizCreate />
            </PrivateRoute>
          }
        />
        <Route
          path="/join"
          element={
            <PrivateRoute>
              <JoinQuiz />
            </PrivateRoute>
          }
        />
        <Route
          path="/results/:attemptId"
          element={
            <PrivateRoute>
              <Results />
            </PrivateRoute>
          }
        />
        <Route path="/" element={<Navigate to={defaultRoute} />} />
      </Routes>
    </Router>
  );
}

export default App;