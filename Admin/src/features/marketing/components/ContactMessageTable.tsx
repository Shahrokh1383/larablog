import type { ContactMessage } from '../types/marketing';

interface Props {
  messages: ContactMessage[];
  onDelete: (id: string) => void;
  isLoading: boolean;
}

export default function ContactMessageTable({ messages, onDelete, isLoading }: Props) {
  return (
    <div className="table-responsive">
      <table className="table table-hover align-middle">
        <thead className="table-light">
          <tr>
            <th>From</th>
            <th>Subject</th>
            <th>Status</th>
            <th>Date</th>
            <th style={{ width: '100px' }}>Actions</th>
          </tr>
        </thead>
        <tbody>
          {messages.map(msg => (
            <tr key={msg.id} className={!msg.is_read ? 'table-warning' : ''}>
              <td>
                <div>{msg.author.name}</div>
                <small className="text-muted">{msg.author.email}</small>
              </td>
              <td><strong>{msg.subject}</strong></td>
              <td>
                <span className={`badge ${msg.is_read ? 'bg-secondary' : 'bg-primary'}`}>
                  {msg.is_read ? 'Read' : 'Unread'}
                </span>
              </td>
              <td>{new Date(msg.created_at).toLocaleString()}</td>
              <td>
                <button 
                  className="btn btn-sm btn-outline-danger" 
                  onClick={() => onDelete(msg.id)} 
                  disabled={isLoading}
                >
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