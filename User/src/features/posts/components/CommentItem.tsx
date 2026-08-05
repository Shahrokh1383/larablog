import type { Comment } from '@/features/comments/types/comment';

interface CommentItemProps {
  comment: Comment;
  onReply: (id: string, name: string) => void;
}

export default function CommentItem({ comment, onReply }: CommentItemProps) {
  const formattedDate = new Date(comment.created_at).toLocaleDateString('en-US', {
    month: 'long', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit'
  });

  return (
    <li className="comment-item">
      <div className="comment-body">
        <div className="comment-avatar">
          <img src={`https://picsum.photos/seed/${comment.author.name}/50/50`} alt={comment.author.name} loading="lazy" />
        </div>
        <div className="comment-content">
          <div className="comment-header">
            <span className="comment-author">{comment.author.name}</span>
            <span className="comment-date">{formattedDate}</span>
          </div>
          <p className="comment-text">{comment.body}</p>
          <button className="comment-reply-btn" onClick={() => onReply(comment.id, comment.author.name)}>
            <i className="fa-sharp fa-solid fa-reply"></i> Reply
          </button>
          
          {/* Nested Replies */}
          {comment.replies && comment.replies.length > 0 && (
            <ul className="comments-list nested-replies mt-3">
              {comment.replies.map((reply) => (
                <CommentItem key={reply.id} comment={reply} onReply={onReply} />
              ))}
            </ul>
          )}
        </div>
      </div>
    </li>
  );
}