import { useState } from 'react';
import { useUsers, useUpdateUserRole, useUpdateUserPassword, UserTable, EditUserRoleModal, EditUserPasswordModal } from '@/features/users';
import type { AdminUser, UpdateRolePayload, UpdatePasswordPayload } from '@/features/users/types/user';

export default function UsersPage() {
  const [page, setPage] = useState(1);
  const { data, isLoading, isError } = useUsers(page);

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

  if (isLoading) return <div className="text-center py-5"><div className="spinner-border" /></div>;
  if (isError) return <div className="alert alert-danger m-4">Failed to load users.</div>;

  return (
    <div className="container py-4">
      <div className="d-flex justify-content-between align-items-center mb-4">
        <h1>User Management</h1>
      </div>

      <div className="card shadow-sm">
        <div className="card-body">
          {data?.data && (
            <UserTable 
              users={data.data} 
              onEditRole={setEditingRoleUser} 
              onEditPassword={setEditingPasswordUser} 
            />
          )}
          
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