'use client';

import type { SavedPostItem } from '@/features/reader/api/readerApi';
import type { PaginatedResponse } from '@/shared/types/api';
import Pagination from './Pagination';

interface BookmarksTabProps {
  savedPosts?: PaginatedResponse<SavedPostItem>;
  isLoading: boolean;
  onPageChange: (page: number) => void;
  onUnsave: (postId: string) => void;
}

export default function BookmarksTab({ savedPosts, isLoading, onPageChange, onUnsave }: BookmarksTabProps) {
  if (isLoading) {
    return (
      <div className="text-center py-5">
        <div className="spinner-border text-primary" role="status" />
      </div>
    );
  }

  if (!savedPosts || savedPosts.data.length === 0) {
    return <div className="text-center py-5 text-muted">No saved posts yet.</div>;
  }

  return (
    <div className="tab-content active" id="bookmarksContent">
      <h3 className="section-title mb-4">Saved Posts</h3>
      <div className="row g-4">
        {savedPosts.data.map((item) => (
          <div key={item.id} className="col-lg-4 col-md-6">
            <article className="post-card post-card-standard">
              <div className="post-card-image">
                <img
                  src={item.post.featured_image || `https://picsum.photos/seed/${item.post.id}/600/350`}
                  alt={item.post.title}
                  loading="lazy"
                />
              </div>
              <div className="post-card-body">
                <div className="post-card-meta">
                  <span className="post-card-date">
                    {new Date(item.saved_at).toLocaleDateString('en-US', { 
                      month: 'short', 
                      day: 'numeric', 
                      year: 'numeric' 
                    })}
                  </span>
                  <span className="post-card-read-time">{item.post.reading_time} min read</span>
                </div>
                <h3 className="post-card-title">
                  <a href={`/post/${item.post.slug}`}>{item.post.title}</a>
                </h3>
                <div className="post-card-footer">
                  <button
                    className="btn-icon-sm remove-bookmark"
                    onClick={() => onUnsave(item.post.id)}
                    aria-label="Remove from saved"
                  >
                    <i className="fa-sharp fa-solid fa-bookmark"></i>
                  </button>
                </div>
              </div>
            </article>
          </div>
        ))}
      </div>

      <Pagination 
        currentPage={savedPosts.meta.current_page} 
        lastPage={savedPosts.meta.last_page} 
        onPageChange={onPageChange} 
      />
    </div>
  );
}