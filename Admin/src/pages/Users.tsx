import { useState, useEffect } from 'react';
import { useUsers, useUpdateUserRole, useUpdateUserPassword, UserTable, EditUserRoleModal, EditUserPasswordModal } from '@/features/users';
import type { AdminUser, UpdateRolePayload, UpdatePasswordPayload } from '@/features/users/types/user';
import { useDebounce } from '@/shared/hooks/useDebounce';

export default function UsersPage() {
  const [page, setPage] = useState(1);
  const [searchTerm, setSearchTerm] = useState('');
  
  // Debounce search to avoid spamming API on every keystroke (500ms delay)
  const debouncedSearch = useDebounce(searchTerm, 500);

  // Reset to page 1 whenever the search term changes
  useEffect(() => {
    setPage(1);
  }, [debouncedSearch]);

  const { data, isLoading, isError } = useUsers(page, debouncedSearch);

  const [editingRoleUser, setEditingRoleUser] = useState<AdminUser | null>(null);
  const [editingPasswordUser, setEditingPasswordUser] = useState<AdminUser | null>(null);

  const roleMutation = useUpdateUserRole();
  const passwordMutation = useUpdateUserPassword();

  const handleUpdateRole = (payload: UpdateRolePayload) => {
    if (!editingRoleUser) return;
    roleMutation.mutate({ userId: editingRoleUser.id, payload }, {
      onSuccess: () => setEditingRoleUser(null),
    });
  };

  const handleUpdatePassword = (payload: UpdatePasswordPayload) => {
    if (!editingPasswordUser) return;
    passwordMutation.mutate({ userId: editingPasswordUser.id, payload }, {
      onSuccess: () => setEditingPasswordUser(null),
    });
  };

  return (
    <div className="container py-4">
      <div className="d-flex justify-content-between align-items-center mb-4">
        <h1>User Management</h1>
      </div>

      <div className="card shadow-sm">
        <div className="card-header bg-white p-3">
          <div className="input-group">
            <span className="input-group-text bg-light border-0">
              <i className="fas fa-search text-muted"></i>
            </span>
            <input
              type="text"
              className="form-control border-0 bg-light"
              placeholder="Search by name or email..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
            />
          </div>
        </div>
        <div className="card-body">
          {isLoading && <div className="text-center py-5"><div className="spinner-border" /></div>}
          {isError && <div className="alert alert-danger m-4">Failed to load users.</div>}
          
          {!isLoading && !isError && data?.data && (
            <UserTable 
              users={data.data} 
              onEditRole={setEditingRoleUser} 
              onEditPassword={setEditingPasswordUser} 
            />
          )}
          
          {!isLoading && !isError && data?.data && data.data.length > 0 && (
            <div className="d-flex justify-content-center mt-4">
              <nav>
                <ul className="pagination">
                  <li className={`page-item ${page <= 1 ? 'disabled' : ''}`}>
                    <button className="page-link" onClick={() => setPage(p => Math.max(p - 1, 1))}>Previous</button>
                  </li>
                  <li className="page-item active">
                    <span className="page-link">Page {data?.meta.current_page} of {data?.meta.last_page}</span>
                  </li>
                  <li className={`page-item ${page >= (data?.meta.last_page ?? 1) ? 'disabled' : ''}`}>
                    <button className="page-link" onClick={() => setPage(p => p + 1)}>Next</button>
                  </li>
                </ul>
              </nav>
            </div>
          )}
        </div>
      </div>

      <EditUserRoleModal 
        user={editingRoleUser}
        isLoading={roleMutation.isPending}
        onClose={() => setEditingRoleUser(null)}
        onSubmit={handleUpdateRole}
      />

      <EditUserPasswordModal 
        user={editingPasswordUser}
        isLoading={passwordMutation.isPending}
        onClose={() => setEditingPasswordUser(null)}
        onSubmit={handleUpdatePassword}
      />
    </div>
  );
}