import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { authService } from "../services/api";
import "./AdminDashboard.css";

export default function AdminDashboard() {
  const [activeTab, setActiveTab] = useState("stats");
  const [data, setData] = useState([]);
  const [stats, setStats] = useState(null);
  const [showModal, setShowModal] = useState(false);
  const [editItem, setEditItem] = useState(null);
  const [formData, setFormData] = useState({});
  const [levels, setLevels] = useState([]);
  const [specializations, setSpecializations] = useState([]);
  const navigate = useNavigate();

  const tables = [
    {
      key: "stats",
      label: "Dashboard",
      endpoint: "/admin/stats",
      isStats: true,
    },
    { key: "students", label: "Students", endpoint: "/admin/students" },
    { key: "professors", label: "Professors", endpoint: "/admin/professors" },
    { key: "quizzes", label: "Quizzes", endpoint: "/admin/quizzes" },
    { key: "modules", label: "Modules", endpoint: "/admin/modules" },
  ];

  useEffect(() => {
    const loadLevels = async () => {
      try {
        const response = await fetch(
          "http://localhost:8000/api/academic-levels"
        );
        if (response.ok) {
          const result = await response.json();
          setLevels(result.data || []);
        }
      } catch (error) {
        console.error("Error loading levels:", error);
      }
    };

    const loadSpecializations = async () => {
      try {
        const response = await fetch(
          "http://localhost:8000/api/specializations"
        );
        if (response.ok) {
          const result = await response.json();
          setSpecializations(result.data || []);
        }
      } catch (error) {
        console.error("Error loading specializations:", error);
      }
    };

    loadData();
    loadLevels();
    loadSpecializations();
  }, [activeTab]);

  const loadData = async () => {
    try {
      const table = tables.find((t) => t.key === activeTab);
      console.log(table.endpoint);
      const response = await fetch(
        `http://localhost:8000/api${table.endpoint}`,
        {
          headers: {
            "Content-Type": "application/json",
          },
        }
      );

      if (response.ok) {
        const result = await response.json();
        console.log(result);
        if (table.isStats) {
          setStats(result.data);
          setData([]);
        } else {
          const tableData = result.data?.data || result.data || [];
          setData(Array.isArray(tableData) ? tableData : []);
          setStats(null);
        }
      }
    } catch (error) {
      console.error("Error loading data:", error);
    }
  };

  const handleEdit = (item) => {
    setEditItem(item);
    setFormData(item);
    setShowModal(true);
  };

  const handleAdd = () => {
    setEditItem(null);
    setFormData({});
    setShowModal(true);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      const table = tables.find((t) => t.key === activeTab);
      const url = editItem
        ? `http://localhost:8000/api${table.endpoint}/${editItem.id}`
        : `http://localhost:8000/api${table.endpoint}`;

      const method = editItem ? "PUT" : "POST";

      const response = await fetch(url, {
        method,
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(formData),
      });

      if (response.ok) {
        setShowModal(false);
        loadData();
      } else {
        const error = await response.json();
        alert(error.message || "Operation failed");
      }
    } catch (error) {
      console.error("Error submitting form:", error);
      alert("Operation failed");
    }
  };

  const handleDelete = async (id) => {
    if (!confirm("Are you sure you want to delete this item?")) return;

    try {
      const table = tables.find((t) => t.key === activeTab);
      const response = await fetch(
        `http://localhost:8000/api${table.endpoint}/${id}`,
        {
          method: "DELETE",
          headers: {
            "Content-Type": "application/json",
          },
        }
      );

      if (response.ok) {
        loadData();
      }
    } catch (error) {
      console.error("Error deleting item:", error);
    }
  };

  const handleLogout = async () => {
    try {
      await authService.logout();
    } catch (error) {
      console.error("Logout error:", error);
    } finally {
      localStorage.removeItem("user");
      navigate("/login");
    }
  };

  const renderForm = () => {
    if (activeTab === "students") {
      return (
        <form onSubmit={handleSubmit} className="admin-form">
          <input
            type="text"
            placeholder="First Name"
            value={formData.prenom || ""}
            onChange={(e) =>
              setFormData({ ...formData, prenom: e.target.value })
            }
            required
          />
          <input
            type="text"
            placeholder="Last Name"
            value={formData.nom || ""}
            onChange={(e) => setFormData({ ...formData, nom: e.target.value })}
            required
          />
          <input
            type="email"
            placeholder="Email"
            value={formData.email || ""}
            onChange={(e) =>
              setFormData({ ...formData, email: e.target.value })
            }
            required
          />
          {!editItem && (
            <input
              type="password"
              placeholder="Password"
              value={formData.password || ""}
              onChange={(e) =>
                setFormData({ ...formData, password: e.target.value })
              }
              required
            />
          )}
          <input
            type="text"
            placeholder="Student Number"
            value={formData.numero_etudiant || ""}
            onChange={(e) =>
              setFormData({ ...formData, numero_etudiant: e.target.value })
            }
            required
          />
          <label>Level</label>
          <select
            value={formData.niveau || ""}
            onChange={(e) =>
              setFormData({ ...formData, niveau: e.target.value })
            }
            required
          >
            <option value="">Select Level</option>
            {levels.map((level) => (
              <option key={level} value={level}>
                {level}
              </option>
            ))}
          </select>
          <button type="submit">
            {editItem ? "Update" : "Create"} Student
          </button>
        </form>
      );
    }

    if (activeTab === "professors") {
      return (
        <form onSubmit={handleSubmit} className="admin-form">
          <input
            type="text"
            placeholder="First Name"
            value={formData.prenom || ""}
            onChange={(e) =>
              setFormData({ ...formData, prenom: e.target.value })
            }
            required
          />
          <input
            type="text"
            placeholder="Last Name"
            value={formData.nom || ""}
            onChange={(e) => setFormData({ ...formData, nom: e.target.value })}
            required
          />
          <input
            type="email"
            placeholder="Email"
            value={formData.email || ""}
            onChange={(e) =>
              setFormData({ ...formData, email: e.target.value })
            }
            required
          />
          {!editItem && (
            <input
              type="password"
              placeholder="Password"
              value={formData.password || ""}
              onChange={(e) =>
                setFormData({ ...formData, password: e.target.value })
              }
              required
            />
          )}
          <label>Specialization</label>
          <select
            value={formData.specialite || ""}
            onChange={(e) =>
              setFormData({ ...formData, specialite: e.target.value })
            }
            required
          >
            <option value="">Select Specialization</option>
            {specializations.map((spec) => (
              <option key={spec} value={spec}>
                {spec}
              </option>
            ))}
          </select>
          <button type="submit">
            {editItem ? "Update" : "Create"} Professor
          </button>
        </form>
      );
    }

    if (activeTab === "modules") {
      return (
        <form onSubmit={handleSubmit} className="admin-form">
          <input
            type="text"
            placeholder="Module Name"
            value={formData.nom_module || ""}
            onChange={(e) =>
              setFormData({ ...formData, nom_module: e.target.value })
            }
            required
          />
          <textarea
            placeholder="Description"
            value={formData.description || ""}
            onChange={(e) =>
              setFormData({ ...formData, description: e.target.value })
            }
          />
          <button type="submit">{editItem ? "Update" : "Create"} Module</button>
        </form>
      );
    }

    if (activeTab === "quizzes") {
      return (
        <form onSubmit={handleSubmit} className="admin-form">
          <label>Active Status</label>
          <select
            value={formData.actif ? "true" : "false"}
            onChange={(e) =>
              setFormData({ ...formData, actif: e.target.value === "true" })
            }
            required
          >
            <option value="true">Yes</option>
            <option value="false">No</option>
          </select>
          <button type="submit">Update Quiz</button>
        </form>
      );
    }
  };

  const renderStats = () => {
    if (!stats) return null;

    return (
      <div className="stats-grid">
        <div className="stat-card">
          <div className="stat-number">{stats.total_students}</div>
          <div className="stat-label">Students</div>
        </div>
        <div className="stat-card">
          <div className="stat-number">{stats.total_professors}</div>
          <div className="stat-label">Professors</div>
        </div>
        <div className="stat-card">
          <div className="stat-number">{stats.total_admins}</div>
          <div className="stat-label">Admins</div>
        </div>
        <div className="stat-card">
          <div className="stat-number">{stats.total_quizzes}</div>
          <div className="stat-label">Total Quizzes</div>
        </div>
        <div className="stat-card">
          <div className="stat-number">{stats.total_groups}</div>
          <div className="stat-label">Groups</div>
        </div>
        <div className="stat-card">
          <div className="stat-number">{stats.total_modules}</div>
          <div className="stat-label">Modules</div>
        </div>
      </div>
    );
  };

  const renderTableData = () => {
    if (activeTab === "stats") return renderStats();
    if (!data.length) return <div className="no-data">No data available</div>;

    const firstItem = data[0];
    const columns = Object.keys(firstItem).filter(
      (key) =>
        !["password", "remember_token", "created_at", "updated_at"].includes(
          key
        )
    );

    return (
      <div className="table-container">
        <table className="admin-table">
          <thead>
            <tr>
              {columns.map((col) => (
                <th key={col}>{col.replace("_", " ").toUpperCase()}</th>
              ))}
              <th>ACTIONS</th>
            </tr>
          </thead>
          <tbody>
            {data.map((item) => (
              <tr key={item.id}>
                {columns.map((col) => (
                  <td key={col}>
                    {col === "actif"
                      ? item[col]
                        ? "Yes"
                        : "No"
                      : typeof item[col] === "boolean"
                      ? item[col]
                        ? "Yes"
                        : "No"
                      : typeof item[col] === "object" && item[col] !== null
                      ? JSON.stringify(item[col]).substring(0, 50)
                      : String(item[col] || "").substring(0, 50)}
                  </td>
                ))}
                <td className="actions">
                  <button onClick={() => handleEdit(item)} className="btn-edit">
                    Edit
                  </button>
                  <button
                    onClick={() => handleDelete(item.id)}
                    className="btn-delete"
                  >
                    Delete
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    );
  };

  return (
    <div className="admin-dashboard">
      <header className="admin-header">
        <h1>Admin Dashboard</h1>
        <button onClick={handleLogout} className="btn-logout">
          Logout
        </button>
      </header>

      <nav className="admin-nav">
        {tables.map((table) => (
          <button
            key={table.key}
            onClick={() => setActiveTab(table.key)}
            className={`nav-btn ${activeTab === table.key ? "active" : ""}`}
          >
            {table.label}
          </button>
        ))}
      </nav>

      <main className="admin-main">
        <div className="table-header">
          <h2>{tables.find((t) => t.key === activeTab)?.label}</h2>
          {activeTab !== "stats" && activeTab !== "quizzes" && (
            <button onClick={handleAdd} className="btn-add">
              Add New
            </button>
          )}
        </div>

        {renderTableData()}
      </main>

      {showModal && (
        <div className="modal-overlay" onClick={() => setShowModal(false)}>
          <div className="modal" onClick={(e) => e.stopPropagation()}>
            <div className="modal-header">
              <h3>
                {editItem ? "Edit" : "Add"}{" "}
                {tables.find((t) => t.key === activeTab)?.label}
              </h3>
              <button onClick={() => setShowModal(false)} className="btn-close">
                ×
              </button>
            </div>
            <div className="modal-body">{renderForm()}</div>
          </div>
        </div>
      )}
    </div>
  );
}
