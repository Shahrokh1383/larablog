import type { Comment } from '../types/comment';

interface CommentItemProps {
  comment: Comment;
  isAdmin: boolean;
  onApprove: (id: string) => void;
  onDelete: (id: string) => void;
}

export default function CommentItem({ comment, isAdmin, onApprove, onDelete }: CommentItemProps) {
  return (
    <div className="d-flex align-items-start border-bottom py-3">
      <div className="flex-grow-1">
        <div className="d-flex justify-content-between align-items-center mb-1">
          <strong>{comment.author.name}</strong>
          <small className="text-muted">
            {new Date(comment.created_at).toLocaleString()}
          </small>
        </div>
        <p className="mb-1">{comment.body}</p>
        <div>
          {comment.is_approved ? (
            <span className="badge bg-success me-2">Approved</span>
          ) : (
            <span className="badge bg-warning text-dark me-2">Pending</span>
          )}
          {isAdmin && !comment.is_approved && (
            <button
              className="btn btn-sm btn-outline-success me-1"
              onClick={() => onApprove(comment.id)}
            >
              <i className="fas fa-check"></i> Approve
            </button>
          )}
          {isAdmin && (
            <button
              className="btn btn-sm btn-outline-danger"
              onClick={() => onDelete(comment.id)}
            >
              <i className="fas fa-trash"></i> Delete
            </button>
          )}
        </div>
      </div>
    </div>
  );
}