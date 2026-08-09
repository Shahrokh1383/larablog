import { useState } from 'react';
import type { Comment } from '@/features/comments/types/comment';

interface CommentItemProps {
  comment: Comment;
  onReply: (id: string, name: string) => void;
  onLoadMoreReplies?: (commentId: string) => void;
  fetchingReplyId?: string | null;
}

function AvatarPlaceholder({ name }: { name: string }) {
  const initials = name.split(' ').map(n => n[0]).join('').slice(0, 2).toUpperCase();
  return (
    <div className="comment-avatar-placeholder" aria-hidden="true">
      {initials}
    </div>
  );
}

export default function CommentItem({ comment, onReply, onLoadMoreReplies, fetchingReplyId }: CommentItemProps) {
  const [isCollapsed, setIsCollapsed] = useState(true);

  const formattedDate = new Date(comment.created_at).toLocaleDateString('en-US', {
    month: 'long', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit'
  });

  const isLoadingReplies = fetchingReplyId === comment.id;
  
  const allReplies = comment.replies || [];
  const previewLimit = 2;
  
  const visibleReplies = isCollapsed ? allReplies.slice(0, previewLimit) : allReplies;
  const hasExtraReplies = (comment.replies_count || 0) > previewLimit || allReplies.length > previewLimit;
  const hiddenRepliesCount = (comment.replies_count || 0) - visibleReplies.length;

  const handleToggleReplies = () => {
    if (isCollapsed) {
      if (comment.replies_has_more) {
        onLoadMoreReplies?.(comment.id);
      }
      setIsCollapsed(false);
    } else {
      setIsCollapsed(true);
    }
  };

  return (
    <li className="comment-item">
      <div className="comment-body">
        <div className="comment-avatar">
          {comment.author.avatar ? (
            <img src={comment.author.avatar} alt={comment.author.name}      loading="lazy" />
          ) : (
            <AvatarPlaceholder name={comment.author.name} />
          )}
        </div>
        <div className="comment-content">
          <div className="comment-header">
            <span className="comment-author">{comment.author.name}</span>
            <span className="comment-date">{formattedDate}</span>
          </div>
          <p className="comment-text">{comment.body}</p>
          
          {/* Button Group: Stacked Vertically */}
          <div className="d-flex flex-column align-items-start gap-2 mb-2">
            {hasExtraReplies && (
              <button 
                className="comment-reply-btn view-replies-btn" 
                onClick={handleToggleReplies}
                disabled={isLoadingReplies}
              >
                {isLoadingReplies ? (
                  <>
                    <span className="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    Loading...
                  </>
                ) : isCollapsed ? (
                  `View replies (${hiddenRepliesCount > 0 ? hiddenRepliesCount : ''})`
                ) : (
                  'Hide replies'
                )}
              </button>
            )}
            
            <button className="comment-reply-btn" onClick={() => onReply(comment.id, comment.author.name)}>
              <i className="fa-sharp fa-solid fa-reply"></i> Reply
            </button>
          </div>
          
          {/* Nested Replies */}
          {visibleReplies.length > 0 && (
            <ul className="comments-list nested-replies mt-3">
              {visibleReplies.map((reply) => (
                <CommentItem 
                  key={reply.id} 
                  comment={reply} 
                  onReply={onReply} 
                  onLoadMoreReplies={onLoadMoreReplies}
                  fetchingReplyId={fetchingReplyId}
                />
              ))}
            </ul>
          )}
        </div>
      </div>
    </li>
  );
}