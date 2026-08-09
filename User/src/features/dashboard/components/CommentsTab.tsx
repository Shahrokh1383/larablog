'use client';

import type { User } from '@/features/auth/types/auth';
import type { UserCommentItem } from '../api/dashboardApi';
import type { PaginatedResponse } from '@/shared/types/api';
import Pagination from './Pagination';

interface CommentsTabProps {
  user: User;
  comments?: PaginatedResponse<UserCommentItem>;
  isLoading: boolean;
  onPageChange: (page: number) => void;
}

export default function CommentsTab({ user, comments, isLoading, onPageChange }: CommentsTabProps) {
  if (isLoading) {
    return (
      <div className="text-center py-5">
        <div className="spinner-border text-primary" role="status" />
      </div>
    );
  }

  if (!comments || comments.data.length === 0) {
    return <div className="text-center py-5 text-muted">No comments yet.</div>;
  }

  return (
    <div className="tab-content active" id="commentsContent">
      <h3 className="section-title mb-4">Your Comments</h3>
      <div className="comments-list">
        {comments.data.map((comment) => (
          <div key={comment.id} className="comment-item">
            <div className="comment-avatar">
              {user?.avatar ? (
                <img src={user.avatar} alt={user.name ?? 'User'} />
              ) : (
                <div
                  className="profile-avatar-fallback"
                  style={{ width: 44, height: 44, fontSize: '1rem' }}
                >
                  {user?.name?.charAt(0).toUpperCase() || 'U'}
                </div>
              )}
            </div>
            <div className="comment-body">
              <div className="comment-meta">
                <strong>{user?.name}</strong> on{' '}
                <a href={`/post/${comment.post.slug}`}>{comment.post.title}</a>
                <span className="comment-date">
                  {new Date(comment.created_at).toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric',
                  })}
                </span>
              </div>
              <p className="comment-text">{comment.body}</p>
            </div>
          </div>
        ))}
      </div>

      <Pagination 
        currentPage={comments.meta.current_page} 
        lastPage={comments.meta.last_page} 
        onPageChange={onPageChange} 
      />
    </div>
  );
}