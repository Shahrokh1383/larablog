import type { AdminUser } from '../types/user';

interface UserTableProps {
  users: AdminUser[];
  onEditRole: (user: AdminUser) => void;
  onEditPassword: (user: AdminUser) => void;
  onDelete: (user: AdminUser) => void;
}

export default function UserTable({ users, onEditRole, onEditPassword, onDelete }: UserTableProps) {
  return (
    <div className="table-responsive">
      <table className="table table-hover align-middle">
        <thead className="table-light">
          <tr>
            <th>User</th>
            <th>Email</th>
            <th>Username</th>
            <th>Role</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          {users.map((user) => (
            <tr key={user.id}>
              <td>
                <div className="d-flex align-items-center">
                  <div 
                    className="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                    style={{ width: 40, height: 40, fontSize: '16px', fontWeight: 600 }}
                  >
                    {user.name.charAt(0).toUpperCase()}
                  </div>
                  {user.name}
                </div>
              </td>
              <td>{user.email}</td>
              <td>{user.username || '—'}</td>
              <td>
                <span className="badge bg-primary">{user.roles[0] || 'N/A'}</span>
              </td>
              <td>
                <button 
                  className="btn btn-sm btn-outline-secondary me-2" 
                  onClick={() => onEditRole(user)}
                >
                  <i className="fas fa-user-tag me-1"></i> Role
                </button>
                <button 
                  className="btn btn-sm btn-outline-warning me-2" 
                  onClick={() => onEditPassword(user)}
                >
                  <i className="fas fa-key me-1"></i> Password
                </button>
                <button 
                  className="btn btn-sm btn-outline-danger" 
                  onClick={() => onDelete(user)}
                >
                  <i className="fas fa-trash me-1"></i> Delete
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}