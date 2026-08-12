import { useState } from 'react';
import { useEligibleUsers } from '../hooks/useEligibleUsers';
import type { TeamMember } from '../types/about';

interface Props {
  isOpen: boolean;
  member: TeamMember | null;
  isLoading: boolean;
  onClose: () => void;
  onCreate: (userIds: string[]) => void; // Changed to support multiple
  onUpdate: (payload: { user_id: string; sort_order: number; is_active: boolean }) => void;
}

export default function TeamMemberFormModal({ isOpen, member, isLoading, onClose, onCreate, onUpdate }: Props) {
  const {
    search,
    setSearch,
    page,
    setPage,
    data,
    isLoading: isLoadingUsers,
  } = useEligibleUsers();

  const [selectedUserIds, setSelectedUserIds] = useState<string[]>([]);
  const [sortOrder, setSortOrder] = useState(member?.sort_order || 0);
  const [isActive, setIsActive] = useState(member ? member.is_active : true);

  if (!isOpen) return null;

  const handleToggleUser = (userId: string) => {
    if (member) return; // Don't allow changing user in edit mode
    setSelectedUserIds((prev) =>
      prev.includes(userId) ? prev.filter((id) => id !== userId) : [...prev, userId]
    );
  };

  const handleSubmit = (e: React.SyntheticEvent<HTMLFormElement>) => {
    e.preventDefault();
    if (member) {
      onUpdate({ user_id: member.user_id, sort_order: sortOrder, is_active: isActive });
    } else {
      onCreate(selectedUserIds);
    }
  };

  return (
    <div className="modal fade show d-block" tabIndex={-1} style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
      <div className="modal-dialog modal-lg">
        <div className="modal-content">
          <div className="modal-header">
            <h5 className="modal-title">{member ? 'Edit Team Member' : 'Add Team Members'}</h5>
            <button type="button" className="btn-close" onClick={onClose}></button>
          </div>
          <form onSubmit={handleSubmit}>
            <div className="modal-body">
              {!member ? (
                <>
                  <div className="mb-3">
                    <label className="form-label">Search & Select Users</label>
                    <input
                      type="text"
                      className="form-control"
                      placeholder="Search by name or email..."
                      value={search}
                      onChange={(e) => {
                        setSearch(e.target.value);
                        setPage(1);
                      }}
                    />
                  </div>

                  <div className="mb-3" style={{ maxHeight: '300px', overflowY: 'auto' }}>
                    {isLoadingUsers && (
                      <div className="text-center py-3">
                        <div className="spinner-border spinner-border-sm" />
                      </div>
                    )}
                    {data?.data && data.data.length > 0
                      ? data.data.map((user) => (
                          <div
                            key={user.id}
                            className={`p-2 border rounded mb-1 d-flex align-items-center ${
                              selectedUserIds.includes(user.id) ? 'bg-primary text-white' : ''
                            }`}
                            style={{ cursor: 'pointer' }}
                            onClick={() => handleToggleUser(user.id)}
                          >
                            {user.avatar ? (
                              <img src={user.avatar} alt="" className="rounded-circle me-2" width="40" height="40" />
                            ) : (
                              <div
                                className="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-2"
                                style={{ width: 40, height: 40 }}
                              >
                                {user.name.charAt(0).toUpperCase()}
                              </div>
                            )}
                            <div>
                              <strong>{user.name}</strong> <small>({user.email})</small>
                              <div className="small">{user.roles.join(', ')}</div>
                            </div>
                          </div>
                        ))
                      : !isLoadingUsers && <p className="text-muted">No users found.</p>}
                  </div>

                  {data?.meta && data.meta.last_page > 1 && (
                    <div className="d-flex justify-content-center mb-3">
                      <nav>
                        <ul className="pagination pagination-sm">
                          <li className={`page-item ${page <= 1 ? 'disabled' : ''}`}>
                            <button className="page-link" type="button" onClick={() => setPage((p) => Math.max(p - 1, 1))}>
                              Previous
                            </button>
                          </li>
                          <li className="page-item disabled">
                            <span className="page-link">
                              Page {page} of {data.meta.last_page}
                            </span>
                          </li>
                          <li className={`page-item ${page >= data.meta.last_page ? 'disabled' : ''}`}>
                            <button className="page-link" type="button" onClick={() => setPage((p) => p + 1)}>
                              Next
                            </button>
                          </li>
                        </ul>
                      </nav>
                    </div>
                  )}
                </>
              ) : (
                <div className="mb-3">
                  <label className="form-label">User</label>
                  <input type="text" className="form-control" value={member.user?.name || ''} disabled />
                </div>
              )}

              <div className="row">
                <div className="col-md-6 mb-3">
                  <label className="form-label">Sort Order</label>
                  <input
                    type="number"
                    className="form-control"
                    value={sortOrder}
                    onChange={(e) => setSortOrder(Number(e.target.value))}
                    min="0"
                  />
                </div>
                <div className="col-md-6 mb-3 d-flex align-items-end">
                  <div className="form-check">
                    <input
                      className="form-check-input"
                      type="checkbox"
                      checked={isActive}
                      onChange={(e) => setIsActive(e.target.checked)}
                      id="isActiveCheck"
                    />
                    <label className="form-check-label" htmlFor="isActiveCheck">
                      Active
                    </label>
                  </div>
                </div>
              </div>
            </div>
            <div className="modal-footer">
              <button type="button" className="btn btn-secondary" onClick={onClose}>
                Cancel
              </button>
              <button type="submit" className="btn btn-primary" disabled={isLoading || (!member && selectedUserIds.length === 0)}>
                {isLoading ? 'Saving...' : 'Save'}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}