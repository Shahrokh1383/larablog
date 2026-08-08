import { useParams } from 'react-router-dom';
import { useState } from 'react';
import { useComments, useReply, useApprove, useDelete, CommentList } from '@/features/comments';
import { useAdminAuth } from '@/features/auth';

export default function PostCommentsPage() {
  const { postId } = useParams<{ postId: string }>();
  const [page, setPage] = useState(1);
  const { data, isLoading, isError } = useComments(postId!, page);
  const { user } = useAdminAuth();
  const isAdmin = user?.roles?.includes('admin') ?? false;

  const replyMutation = useReply(postId!);
  const approveMutation = useApprove(postId!);
  const deleteMutation = useDelete(postId!);

  const comments = data?.data ?? [];
  const meta = data?.meta;

  const handleApprove = (commentId: string) => {
    approveMutation.mutate(commentId);
  };

  const handleDelete = (commentId: string) => {
    if (window.confirm('Delete this comment?')) {
      deleteMutation.mutate(commentId);
    }
  };

  return (
    <div className="container py-4">
      <h1 className="mb-4">Comments for Post</h1>
      <CommentList
        comments={comments}
        isLoading={isLoading}
        isError={isError}
        isAdmin={isAdmin}
        onApprove={handleApprove}
        onDelete={handleDelete}
      />
      {meta && (
        <div className="d-flex justify-content-center mt-4">
          <nav>
            <ul className="pagination">
              <li className={`page-item ${page <= 1 ? 'disabled' : ''}`}>
                <button className="page-link" onClick={() => setPage(p => Math.max(p - 1, 1))}>
                  Previous
                </button>
              </li>
              <li className="page-item active">
                <span className="page-link">Page {meta.current_page} of {meta.last_page}</span>
              </li>
              <li className={`page-item ${page >= (meta.last_page ?? 1) ? 'disabled' : ''}`}>
                <button className="page-link" onClick={() => setPage(p => p + 1)}>
                  Next
                </button>
              </li>
            </ul>
          </nav>
        </div>
      )}
    </div>
  );
}