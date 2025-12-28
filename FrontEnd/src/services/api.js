import axios from 'axios';

const API_BASE_URL = 'http://localhost:8000/api';

const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('authToken');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('authToken');
      localStorage.removeItem('user');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export const authService = {
  register: (data) => apiClient.post('/auth/register', data),
  login: (data) => apiClient.post('/auth/login', data),
  logout: () => apiClient.post('/auth/logout'),
  getProfile: () => apiClient.get('/auth/profile'),
  updateProfile: (data) => apiClient.put('/auth/profile', data),
  refreshToken: () => apiClient.post('/auth/refresh-token'),
  getGroups: () => apiClient.get('/groups'),
  getSpecializations: () => apiClient.get('/specializations'),
};

export const moduleService = {
  getAllModules: () => apiClient.get('/modules'),
};

export const quizService = {
  getAllQuizzes: (params) => apiClient.get('/quizzes', { params }),
  getQuiz: (id) => apiClient.get(`/quizzes/${id}`),

  getQuizByCode: (code) => apiClient.get(`/quizzes/code/${code}`),
  
  createQuiz: (data) => apiClient.post('/quizzes', data),
  updateQuiz: (id, data) => apiClient.put(`/quizzes/${id}`, data),
  deleteQuiz: (id) => apiClient.delete(`/quizzes/${id}`),
  getQuizStatistics: (id) => apiClient.get(`/quizzes/${id}/statistics`),
  getQuizzesByGroup: (groupId) => apiClient.get(`/quizzes/group/${groupId}`),
};

export const questionService = {
  getAllQuestions: (params) => apiClient.get('/questions', { params }),
  getQuestion: (id) => apiClient.get(`/questions/${id}`),
  createQuestion: (data) => apiClient.post('/questions', data),
  bulkCreateQuestions: (data) => apiClient.post('/questions/bulk', data),
  updateQuestion: (id, data) => apiClient.put(`/questions/${id}`, data),
  deleteQuestion: (id) => apiClient.delete(`/questions/${id}`),
  getQuestionsByQuiz: (quizId) => apiClient.get(`/questions/quiz/${quizId}`),
};

export const attemptService = {
  getAllAttempts: (params) => apiClient.get('/attempts', { params }),
  getAttempt: (id) => apiClient.get(`/attempts/${id}`),
  startAttempt: (data) => apiClient.post('/attempts', data),
  updateAttempt: (id, data) => apiClient.put(`/attempts/${id}`, data),
  deleteAttempt: (id) => apiClient.delete(`/attempts/${id}`),
  finishAttempt: (id) => apiClient.post(`/attempts/${id}/finish`),
  calculateScore: (id) => apiClient.post(`/attempts/${id}/calculate-score`),
  getStudentAttempts: (studentId) => apiClient.get(`/attempts/student/${studentId}`),
  getQuizAttempts: (quizId) => apiClient.get(`/attempts/quiz/${quizId}`),
};

export const responseService = {
  getAllResponses: (params) => apiClient.get('/responses', { params }),
  getResponse: (id) => apiClient.get(`/responses/${id}`),
  submitResponse: (data) => apiClient.post('/responses', data),
  bulkSubmitResponses: (data) => apiClient.post('/responses/bulk', data),
  updateResponse: (id, data) => apiClient.put(`/responses/${id}`, data),
  deleteResponse: (id) => apiClient.delete(`/responses/${id}`),
  getAttemptResponses: (attemptId) => apiClient.get(`/responses/attempt/${attemptId}`),
  getAttemptStatistics: (attemptId) => apiClient.get(`/responses/attempt/${attemptId}/statistics`),
};

export const choiceService = {
  getAllChoices: (params) => apiClient.get('/choices', { params }),
  getChoice: (id) => apiClient.get(`/choices/${id}`),
  createChoice: (data) => apiClient.post('/choices', data),
  bulkCreateChoices: (data) => apiClient.post('/choices/bulk', data),
  updateChoice: (id, data) => apiClient.put(`/choices/${id}`, data),
  deleteChoice: (id) => apiClient.delete(`/choices/${id}`),
  getQuestionChoices: (questionId) => apiClient.get(`/choices/question/${questionId}`),
};

export default apiClient;
