'use client';

import { useState } from 'react';
import type { UseInfiniteQueryResult, InfiniteData } from '@tanstack/react-query';
import type { CommentsResponse } from '@/features/comments/api/commentsApi';
import CommentItem from './CommentItem';
import CommentForm from './CommentForm';

interface CommentsSectionProps {
  postId: string;
  // Fix: Use InfiniteData<CommentsResponse> for React Query v5 infinite queries
  commentsQuery: UseInfiniteQueryResult<InfiniteData<CommentsResponse>, Error>;
  onLoadMoreReplies: (commentId: string) => void;
  fetchingReplyId?: string | null;
}

export default function CommentsSection({ postId, commentsQuery, onLoadMoreReplies, fetchingReplyId }: CommentsSectionProps) {
  const [replyTo, setReplyTo] = useState<{ id: string; name: string } | null>(null);

  const { data, isLoading, isError, fetchNextPage, hasNextPage, isFetchingNextPage } = commentsQuery;

  // data is now correctly typed as InfiniteData<CommentsResponse> | undefined
  const comments = data?.pages.flatMap(page => page.data) ?? [];
  const totalComments = data?.pages[0]?.meta.total ?? 0;

  const handleReply = (id: string, name: string) => {
    setReplyTo({ id, name });
    document.getElementById('commentFormWrapper')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
  };

  return (
    <section className="comments-section" id="comments">
      <div className="section-header">
        <h2 className="comments-title">Comments <span className="text-gradient">({totalComments})</span></h2>
        <p className="comments-desc">Join the discussion and share your thoughts.</p>
      </div>

      {isLoading ? (
        <div className="text-center py-4"><div className="spinner-border text-primary"></div></div>
      ) : isError ? (
        <p className="text-danger text-center">Failed to load comments.</p>
      ) : (
        <>
          <ul className="comments-list" id="commentsList">
            {comments.map((comment) => (
              <CommentItem 
                key={comment.id} 
                comment={comment} 
                onReply={handleReply} 
                onLoadMoreReplies={onLoadMoreReplies}
                fetchingReplyId={fetchingReplyId}
              />
            ))}
            {comments.length === 0 && <p className="text-muted text-center py-4">No comments yet. Be the first to comment!</p>}
          </ul>

          {hasNextPage && (
            <div className="text-center mt-4">
              <button 
                className="btn btn-outline-primary btn-lg" 
                onClick={() => fetchNextPage()} 
                disabled={isFetchingNextPage}
              >
                {isFetchingNextPage ? (
                  <>
                    <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Loading...
                  </>
                ) : 'Load More Comments'}
              </button>
            </div>
          )}
        </>
      )}

      <CommentForm postId={postId} replyTo={replyTo} onClearReply={() => setReplyTo(null)} />
    </section>
  );
}