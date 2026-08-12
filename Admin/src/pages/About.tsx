import { useState } from 'react';
import {
  useSiteSettings,
  useUpdateSiteSettings,
  useTeamMembers,
  useCreateTeamMember,
  useUpdateTeamMember,
  useDeleteTeamMember,
  SiteSettingsForm,
  TeamMemberTable,
  TeamMemberFormModal,
} from '@/features/about';
import type { TeamMember } from '@/features/about/types/about';

export default function AboutPage() {
  // Settings
  const { data: settingsData, isLoading: settingsLoading } = useSiteSettings();
  const updateSettings = useUpdateSiteSettings();

  // Team members
  const [page, setPage] = useState(1);
  const { data: membersData, isLoading: membersLoading, isError: membersError } = useTeamMembers(page, 10);
  const createMember = useCreateTeamMember();
  const updateMember = useUpdateTeamMember();
  const deleteMember = useDeleteTeamMember();

  // Modal state
  const [editingMember, setEditingMember] = useState<TeamMember | null>(null);
  const [showModal, setShowModal] = useState(false);

  const openCreate = () => {
    setEditingMember(null);
    setShowModal(true);
  };
  const openEdit = (member: TeamMember) => {
    setEditingMember(member);
    setShowModal(true);
  };
  const closeModal = () => {
    setShowModal(false);
    setEditingMember(null);
  };

  const handleSettingsSubmit = (data: any) => {
    updateSettings.mutate(data);
  };

  const handleTeamMemberSubmit = (payload: { user_id: string; sort_order: number; is_active: boolean }) => {
    if (editingMember) {
      updateMember.mutate(
        { id: editingMember.id, ...payload },
        { onSuccess: () => closeModal() }
      );
    } else {
      createMember.mutate(payload, { onSuccess: () => closeModal() });
    }
  };

  const handleDelete = (id: string) => {
    if (confirm('Are you sure you want to delete this team member?')) {
      deleteMember.mutate(id);
    }
  };

  return (
    <div className="container py-4">
      <h1 className="mb-4">About Page Settings</h1>

      <div className="row">
        {/* Site Settings */}
        <div className="col-lg-5 mb-4">
          <div className="card shadow-sm">
            <div className="card-header bg-white">
              <h5 className="mb-0">Site Information</h5>
            </div>
            <div className="card-body">
              <SiteSettingsForm
                settings={settingsData?.data}
                isLoading={settingsLoading}
                isSaving={updateSettings.isPending}
                onSubmit={handleSettingsSubmit}
              />
            </div>
          </div>
        </div>

        {/* Team Members */}
        <div className="col-lg-7">
          <div className="card shadow-sm">
            <div className="card-header bg-white d-flex justify-content-between align-items-center">
              <h5 className="mb-0">Team Members</h5>
              <button className="btn btn-primary btn-sm" onClick={openCreate}>
                <i className="fas fa-plus me-1"></i> Add Member
              </button>
            </div>
            <div className="card-body">
              {membersLoading && (
                <div className="text-center py-3">
                  <div className="spinner-border" />
                </div>
              )}
              {membersError && <div className="alert alert-danger">Failed to load team members.</div>}
              {!membersLoading && !membersError && membersData && (
                <>
                  <TeamMemberTable members={membersData.data} onEdit={openEdit} onDelete={handleDelete} />
                  {membersData.meta.last_page > 1 && (
                    <div className="d-flex justify-content-center mt-3">
                      <nav>
                        <ul className="pagination">
                          <li className={`page-item ${page <= 1 ? 'disabled' : ''}`}>
                            <button className="page-link" onClick={() => setPage((p) => Math.max(p - 1, 1))}>
                              Previous
                            </button>
                          </li>
                          <li className="page-item active">
                            <span className="page-link">
                              Page {membersData.meta.current_page} of {membersData.meta.last_page}
                            </span>
                          </li>
                          <li className={`page-item ${page >= membersData.meta.last_page ? 'disabled' : ''}`}>
                            <button className="page-link" onClick={() => setPage((p) => p + 1)}>
                              Next
                            </button>
                          </li>
                        </ul>
                      </nav>
                    </div>
                  )}
                </>
              )}
            </div>
          </div>
        </div>
      </div>

      {showModal && (
        <TeamMemberFormModal
          isOpen={showModal}
          member={editingMember}
          isLoading={editingMember ? updateMember.isPending : createMember.isPending}
          onClose={closeModal}
          onSubmit={handleTeamMemberSubmit}
        />
      )}
    </div>
  );
}