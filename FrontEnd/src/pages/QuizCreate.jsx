import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { quizService, questionService, choiceService, moduleService } from '../services/api';
import './QuizCreate.css';

function makeEmptyQuestion() {
  return {
    enonce: '',
    type_question: 'QCM4',
    points: 1,
    choices: [
      { texte_choix: '', est_correct: false },
      { texte_choix: '', est_correct: false },
      { texte_choix: '', est_correct: false },
      { texte_choix: '', est_correct: false },
    ],
  };
}

export default function QuizCreate() {
  const [meta, setMeta] = useState({ module_id: '', titre: '', description: '', duree: 30, date_debut_disponibilite: '', date_fin_disponibilite: '', afficher_correction: false, nombre_tentatives_max: 1, actif: true });
  const [modules, setModules] = useState([]);
  const [numQuestions, setNumQuestions] = useState(0);
  const [questions, setQuestions] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [result, setResult] = useState(null);
  const navigate = useNavigate();

  useEffect(() => {
    const loadModules = async () => {
      try {
        const res = await moduleService.getAllModules();
        setModules(res.data.data || []);
      } catch (e) {
        console.error('Failed to load modules:', e);
      }
    };
    loadModules();
  }, []);

  const updateMeta = (e) => {
    const { name, value, type, checked } = e.target;
    setMeta(m => ({ ...m, [name]: type === 'checkbox' ? checked : value }));
  };

  const generateQuestions = () => {
    const n = Math.max(0, parseInt(numQuestions, 10) || 0);
    const arr = Array.from({ length: n }, () => makeEmptyQuestion());
    setQuestions(arr);
  };

  const updateQuestion = (idx, field, value) => {
    setQuestions(qs => qs.map((q,i) => i===idx ? ({ ...q, [field]: value }) : q));
  };

  const updateChoice = (qIdx, cIdx, field, value) => {
    setQuestions(qs => qs.map((q,i) => {
      if (i!==qIdx) return q;
      const choices = q.choices.map((c,j) => j===cIdx ? ({ ...c, [field]: value }) : c);
      return { ...q, choices };
    }));
  };

  const changeTypeAdjustChoices = (idx, type) => {
    setQuestions(qs => qs.map((q,i) => {
      if (i!==idx) return q;
      if (type === 'QCM3') return { ...q, type_question: type, choices: q.choices.slice(0,3) };
      const choices = q.choices.length >=4 ? q.choices : [...q.choices, ...Array.from({length: 4 - q.choices.length}, ()=>({ texte_choix:'', est_correct:false }))];
      return { ...q, type_question: type, choices };
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true); setError(''); setResult(null);
    try {
      // create quiz first
      const quizRes = await quizService.createQuiz(meta);
      const quiz = quizRes.data.data;

      if (questions.length > 0) {
        // prepare questions payload
        const payload = {
          quiz_id: quiz.id,
          questions: questions.map(q => ({ enonce: q.enonce, type_question: q.type_question, points: q.points }))
        };

        const qRes = await questionService.bulkCreateQuestions(payload);
        const created = qRes.data.data;

        // create choices per question
        for (let i=0;i<created.length;i++) {
          const qId = created[i].id;
          const qs = questions[i];
          if (!qs || !qs.choices) continue;
          const choicesPayload = { question_id: qId, choices: qs.choices.map(c => ({ texte_choix: c.texte_choix, est_correct: !!c.est_correct })) };
          await choiceService.bulkCreateChoices(choicesPayload);
        }
      }

      setResult(quiz);
    } catch (err) {
      console.error('Quiz creation error:', err.response?.data);
      const errData = err.response?.data;
      if (errData?.errors) {
        const errMsgs = Object.entries(errData.errors).map(([key, msgs]) => `${key}: ${msgs.join(', ')}`).join('\n');
        setError(`Validation failed:\n${errMsgs}`);
      } else {
        setError(errData?.message || 'Failed to create quiz and questions');
      }
    } finally { setLoading(false); }
  };

  const handleCopy = async () => {
    if (result?.code_quiz) {
      await navigator.clipboard.writeText(result.code_quiz);
      alert('Quiz code copied to clipboard');
    }
  };

  return (
    <div className="qc-page">
      <div className="qc-card">
        <h2>Create Quiz + Questions</h2>
        {error && <div className="qc-error">{error}</div>}

        {!result ? (
          <form onSubmit={handleSubmit} className="qc-form">
            <div className="form-group">
              <label>Title</label>
              <input name="titre" value={meta.titre} onChange={updateMeta} required />
            </div>
            <div className="form-group">
              <label>Module</label>
              <select name="module_id" value={meta.module_id} onChange={updateMeta} required>
                <option value="">Select a module</option>
                {modules.map(module => (
                  <option key={module.id} value={module.id}>
                    {module.nom_module}
                  </option>
                ))}
              </select>
            </div>
            <div className="form-row">
              <div className="form-group">
                <label>Duration (minutes)</label>
                <input type="number" name="duree" value={meta.duree} onChange={updateMeta} min="1" required />
              </div>
              <div className="form-group">
                <label>Questions count</label>
                <input type="number" value={numQuestions} onChange={e=>setNumQuestions(e.target.value)} min="0" />
                <button type="button" className="btn-secondary" onClick={generateQuestions}>Generate</button>
              </div>
            </div>

            {/* <div className="form-group">
              <label>Start Date</label>
              <input type="datetime-local" name="date_debut_disponibilite" value={meta.date_debut_disponibilite} onChange={updateMeta} required />
            </div>
            <div className="form-group">
              <label>End Date</label>
              <input type="datetime-local" name="date_fin_disponibilite" value={meta.date_fin_disponibilite} onChange={updateMeta} required />
            </div> */}

            {questions.length > 0 && (
              <div className="questions-block">
                <h3>Questions</h3>
                {questions.map((q, qi) => (
                  <div key={qi} className="question-item">
                    <div className="form-group">
                      <label>Question {qi+1} text</label>
                      <input value={q.enonce} onChange={e=>updateQuestion(qi,'enonce', e.target.value)} required />
                    </div>
                    <div className="form-row">
                      <div className="form-group">
                        <label>Points</label>
                        <input type="number" value={q.points} onChange={e=>updateQuestion(qi,'points', parseFloat(e.target.value))} min="0" />
                      </div>
                      <div className="form-group">
                        <label>Type</label>
                        <select value={q.type_question} onChange={e=>changeTypeAdjustChoices(qi, e.target.value)}>
                          <option value="QCM4">QCM4</option>
                          <option value="QCM3">QCM3</option>
                        </select>
                      </div>
                    </div>
                    <div className="choices-block">
                      <label>Choices</label>
                      {q.choices.map((c, ci) => (
                        <div key={ci} className="choice-row">
                          <input placeholder={`Choice ${ci+1}`} value={c.texte_choix} onChange={e=>updateChoice(qi, ci, 'texte_choix', e.target.value)} required />
                          <label><input type="checkbox" checked={c.est_correct} onChange={e=>updateChoice(qi, ci, 'est_correct', e.target.checked)} /> Correct</label>
                        </div>
                      ))}
                    </div>
                    <hr />
                  </div>
                ))}
              </div>
            )}

            <button className="btn-primary" disabled={loading}>{loading ? 'Creating...' : 'Create Quiz & Questions'}</button>
          </form>
        ) : (
          <div className="qc-result">
            <h3>Quiz created</h3>
            <p><strong>Title:</strong> {result.titre}</p>
            <p><strong>Quiz Code:</strong> <code className="qc-code">{result.code_quiz}</code></p>
            <div className="qc-actions">
              <button type="button" className="btn-primary" onClick={handleCopy}>Copy Code</button>
              <button type="button" className="btn-secondary" onClick={() => navigate(`/quiz/${result.id}`)}>Open Quiz</button>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
