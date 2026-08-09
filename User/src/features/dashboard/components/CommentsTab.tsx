'use client';

import { useAuth } from '@/features/auth/context/AuthContext';
import { useUserComments } from '../hooks/useUserComments';

export default function CommentsTab() {
  const { user } = useAuth();
  const { data, isLoading } = useUserComments();

  if (isLoading) {
    return (
      <div className="text-center py-5">
        <div className="spinner-border text-primary" role="status" />
      </div>
    );
  }

  if (!data || data.data.length === 0) {
    return <div className="text-center py-5 text-muted">No comments yet.</div>;
  }

  return (
    <div className="tab-content active" id="commentsContent">
      <h3 className="section-title mb-4">Your Comments</h3>
      <div className="comments-list">
        {data.data.map((comment) => (
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
    </div>
  );
}