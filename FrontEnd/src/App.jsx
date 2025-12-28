import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import Login from './pages/Login';
import Register from './pages/Register';
import Dashboard from './pages/Dashboard';
import AdminDashboard from './pages/AdminDashboard';
import QuizDetail from './pages/QuizDetail';
import QuizCreate from './pages/QuizCreate';
import JoinQuiz from './pages/JoinQuiz';
import Results from './pages/Results';
import './App.css';

function App() {
  return (
    <Router>
      <Routes>
        <Route path="/login" element={<Login />} />
        <Route path="/register" element={<Register />} />
        <Route path="/dashboard" element={<Dashboard />} />
        <Route path="/admin" element={<AdminDashboard />} />
        <Route path="/quiz/:id" element={<QuizDetail />} />
        <Route path="/quizzes/create" element={<QuizCreate />} />
        <Route path="/join" element={<JoinQuiz />} />
        <Route path="/results/:attemptId" element={<Results />} />
        <Route path="/" element={<Navigate to="/login" />} />
      </Routes>
    </Router>
  );
}

export default App;