import type { Subscriber } from '../types/marketing';

interface Props {
  subscribers: Subscriber[];
  selectedIds: string[];
  onToggleSelect: (id: string) => void;
  onSelectAll: () => void;
  onDelete: (id: string) => void;
  isLoading: boolean;
}

export default function SubscriberTable({ subscribers, selectedIds, onToggleSelect, onSelectAll, onDelete, isLoading }: Props) {
  const allSelected = subscribers.length > 0 && subscribers.every(s => selectedIds.includes(s.id));

  return (
    <div className="table-responsive">
      <table className="table table-hover align-middle">
        <thead className="table-light">
          <tr>
            <th style={{ width: '40px' }}>
              <input type="checkbox" className="form-check-input" checked={allSelected} onChange={onSelectAll} />
            </th>
            <th>Email</th>
            <th>Status</th>
            <th>Subscribed At</th>
            <th style={{ width: '100px' }}>Actions</th>
          </tr>
        </thead>
        <tbody>
          {subscribers.map(sub => (
            <tr key={sub.id}>
              <td>
                <input 
                  type="checkbox" 
                  className="form-check-input" 
                  checked={selectedIds.includes(sub.id)} 
                  onChange={() => onToggleSelect(sub.id)} 
                />
              </td>
              <td>{sub.email}</td>
              <td>
                <span className={`badge ${sub.is_active ? 'bg-success' : 'bg-secondary'}`}>
                  {sub.is_active ? 'Active' : 'Inactive'}
                </span>
              </td>
              <td>{new Date(sub.created_at).toLocaleDateString()}</td>
              <td>
                <button className="btn btn-sm btn-outline-danger" onClick={() => onDelete(sub.id)} disabled={isLoading}>
                  <i className="fas fa-trash"></i>
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}