import { useState } from 'react';
import type { ContactMessage } from '../types/marketing';

interface Props {
  message: ContactMessage | null;
  isLoading: boolean;
  onClose: () => void;
  onSubmit: (replyBody: string) => void;
}

export default function ReplyMessageModal({ message, isLoading, onClose, onSubmit }: Props) {
  const [replyBody, setReplyBody] = useState('');

  if (!message) return null;

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (replyBody.trim()) {
      onSubmit(replyBody);
    }
  };

  return (
    <div className="modal fade show d-block" tabIndex={-1} style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
      <div className="modal-dialog modal-lg">
        <div className="modal-content">
          <form onSubmit={handleSubmit}>
            <div className="modal-header">
              <h5 className="modal-title">Reply to {message.author.name}</h5>
              <button type="button" className="btn-close" onClick={onClose} disabled={isLoading}></button>
            </div>
            <div className="modal-body">
              <div className="mb-3 p-3 bg-light rounded border">
                <strong>Original Subject:</strong> {message.subject}<br/>
                <strong>Original Message:</strong><br/>
                <p className="mb-0 text-muted">{message.message}</p>
              </div>
              <div className="mb-3">
                <label className="form-label">Your Reply (Sent from LaraBlog Support)</label>
                <textarea 
                  className="form-control" 
                  rows={6} 
                  value={replyBody} 
                  onChange={(e) => setReplyBody(e.target.value)}
                  required
                  disabled={isLoading}
                  placeholder="Type your response here..."
                ></textarea>
              </div>
            </div>
            <div className="modal-footer">
              <button type="button" className="btn btn-secondary" onClick={onClose} disabled={isLoading}>Cancel</button>
              <button type="submit" className="btn btn-primary" disabled={isLoading}>
                {isLoading ? 'Sending...' : 'Send Reply'}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}