import type { TeamMember } from '../types/about';

interface Props {
  members: TeamMember[];
  onEdit: (member: TeamMember) => void;
  onDelete: (id: string) => void;
}

export default function TeamMemberTable({ members, onEdit, onDelete }: Props) {
  return (
    <div className="table-responsive">
      <table className="table table-hover align-middle">
        <thead className="table-light">
          <tr>
            <th>Photo</th>
            <th>Name</th>
            <th>Expertise</th>
            <th>Active</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          {members.map((member) => (
            <tr key={member.id}>
              <td>
                {member.user?.avatar ? (
                  <img src={member.user.avatar} alt="" width="40" height="40" className="rounded-circle" />
                ) : (
                  <div className="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style={{ width: 40, height: 40 }}>
                    {member.user?.name?.charAt(0).toUpperCase() || '?'}
                  </div>
                )}
              </td>
              <td>{member.user?.name || 'Unknown'}</td>
              <td>{member.user?.expertise || '-'}</td>
              <td>{member.is_active ? <span className="badge bg-success">Active</span> : <span className="badge bg-secondary">Inactive</span>}</td>
              <td>
                <button className="btn btn-sm btn-outline-primary me-2" onClick={() => onEdit(member)}>Edit</button>
                <button className="btn btn-sm btn-outline-danger" onClick={() => onDelete(member.id)}>Delete</button>
              </td>
            </tr>
          ))}
          {members.length === 0 && (
            <tr><td colSpan={5} className="text-center text-muted py-3">No team members yet.</td></tr>
          )}
        </tbody>
      </table>
    </div>
  );
}