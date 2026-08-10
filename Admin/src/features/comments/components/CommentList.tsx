import CommentItem from './CommentItem';
import type { Comment } from '../types/comment';

interface CommentListProps {
  comments: Comment[];
  isLoading: boolean;
  isError: boolean;
  isAdmin: boolean;
  onApprove: (id: string) => void;
  onDelete: (id: string) => void;
}

export default function CommentList({
  comments,
  isLoading,
  isError,
  isAdmin,
  onApprove,
  onDelete,
}: CommentListProps) {
  if (isLoading) {
    return <div className="text-center py-5"><div className="spinner-border" /></div>;
  }
  if (isError) {
    return <div className="alert alert-danger">Failed to load comments.</div>;
  }
  if (comments.length === 0) {
    return <div className="text-center py-5 text-muted">No comments yet.</div>;
  }

  return (
    <div>
      {comments.map((comment) => (
        <CommentItem
          key={comment.id}
          comment={comment}
          isAdmin={isAdmin}
          onApprove={onApprove}
          onDelete={onDelete}
        />
      ))}
    </div>
  );
}