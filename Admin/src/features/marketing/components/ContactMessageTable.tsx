import type { ContactMessage } from '../types/marketing';

interface Props {
  messages: ContactMessage[];
  onDelete: (id: string) => void;
  onToggleRead: (id: string) => void;
  onReply: (msg: ContactMessage) => void;
  isLoading: boolean;
  isToggling: boolean;
}

export default function ContactMessageTable({ 
  messages, 
  onDelete, 
  onToggleRead, 
  onReply, 
  isLoading, 
  isToggling 
}: Props) {
  return (
    <div className="table-responsive">
      <table className="table table-hover align-middle">
        <thead className="table-light">
          <tr>
            <th>From</th>
            <th>Subject</th>
            <th>Status</th>
            <th>Date</th>
            <th style={{ width: '140px' }}>Actions</th>
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
                <div className="d-flex gap-1">
                  <button 
                    className={`btn btn-sm ${msg.is_read ? 'btn-outline-secondary' : 'btn-outline-success'}`} 
                    onClick={() => onToggleRead(msg.id)} 
                    disabled={isToggling}
                    title={msg.is_read ? 'Mark as Unread' : 'Mark as Read'}
                  >
                    <i className={`fas ${msg.is_read ? 'fa-envelope' : 'fa-envelope-open'}`}></i>
                  </button>
                  <button 
                    className="btn btn-sm btn-outline-primary" 
                    onClick={() => onReply(msg)} 
                    disabled={isLoading}
                    title="Reply to User"
                  >
                    <i className="fas fa-reply"></i>
                  </button>
                  <button 
                    className="btn btn-sm btn-outline-danger" 
                    onClick={() => onDelete(msg.id)} 
                    disabled={isLoading}
                    title="Delete"
                  >
                    <i className="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}