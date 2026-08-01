import type { AdminUser } from '../types/user';

interface UserTableProps {
  users: AdminUser[];
  onEditRole: (user: AdminUser) => void;
  onEditPassword: (user: AdminUser) => void;
}

export default function UserTable({ users, onEditRole, onEditPassword }: UserTableProps) {
  return (
    <div className="table-responsive">
      <table className="table table-hover align-middle">
        <thead className="table-light">
          <tr>
            <th>User</th>
            <th>Email</th>
            <th>Role</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          {users.map((user) => (
            <tr key={user.id}>
              <td>
                <div className="d-flex align-items-center">
                  {user.avatar ? (
                    <img src={user.avatar} alt={user.name} className="rounded-circle me-2" width="40" height="40" />
                  ) : (
                    <div className="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style={{ width: 40, height: 40 }}>
                      {user.name.charAt(0).toUpperCase()}
                    </div>
                  )}
                  {user.name}
                </div>
              </td>
              <td>{user.email}</td>
              <td>
                <span className="badge bg-primary">{user.roles[0] || 'N/A'}</span>
              </td>
              <td>
                <button className="btn btn-sm btn-outline-secondary me-2" onClick={() => onEditRole(user)}>
                  <i className="fas fa-user-tag me-1"></i> Role
                </button>
                <button className="btn btn-sm btn-outline-warning" onClick={() => onEditPassword(user)}>
                  <i className="fas fa-key me-1"></i> Password
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}