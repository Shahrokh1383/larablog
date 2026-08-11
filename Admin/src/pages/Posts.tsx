import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { usePosts, usePostMutations, PostDataTable } from '@/features/posts';
import type { Post } from '@/features/posts';
import { useDebounce } from '@/shared/hooks/useDebounce';

export default function PostsPage() {
  const navigate = useNavigate();
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const [showEditorsPicksOnly, setShowEditorsPicksOnly] = useState(false);

  const debouncedSearch = useDebounce(search, 500);

  // Reset to page 1 when search or filter changes
  useEffect(() => {
    setPage(1);
  }, [debouncedSearch, showEditorsPicksOnly]);

  const { data, isLoading, isError } = usePosts({
    page,
    search: debouncedSearch,
    isEditorsPick: showEditorsPicksOnly ? true : undefined,
  });

  const { deletePost } = usePostMutations();

  const handleDelete = (post: Post) => {
    if (window.confirm(`Delete "${post.title}"?`)) {
      deletePost.mutate(post.id);
    }
  };

  const handleComments = (post: Post) => {
    navigate(`/posts/${post.id}/comments`);
  };

  return (
    <div className="container py-4">
      <div className="d-flex justify-content-between align-items-center mb-4">
        <h1>Posts</h1>
        <button className="btn btn-primary" onClick={() => navigate('/posts/editor')}>
          <i className="fas fa-plus me-2"></i>New Post
        </button>
      </div>

      <div className="card shadow-sm">
        <div className="card-header bg-white p-3">
          <div className="row align-items-center">
            <div className="col-md-8">
              <div className="input-group">
                <span className="input-group-text bg-light border-0">
                  <i className="fas fa-search text-muted"></i>
                </span>
                <input
                  type="text"
                  className="form-control border-0 bg-light"
                  placeholder="Search posts..."
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                />
              </div>
            </div>
            <div className="col-md-4 text-end">
              <div className="form-check form-switch d-inline-block">
                <input
                  className="form-check-input"
                  type="checkbox"
                  id="editorsPickFilter"
                  checked={showEditorsPicksOnly}
                  onChange={(e) => setShowEditorsPicksOnly(e.target.checked)}
                />
                <label className="form-check-label" htmlFor="editorsPickFilter">
                  <i className="fas fa-star text-warning me-1"></i>
                  Editor's Picks only
                </label>
              </div>
            </div>
          </div>
        </div>
        <div className="card-body">
          {isLoading && (
            <div className="text-center py-5">
              <div className="spinner-border" />
            </div>
          )}
          {isError && <div className="alert alert-danger m-4">Failed to load posts.</div>}

          {!isLoading && !isError && data?.data && (
            <PostDataTable
              posts={data.data}
              isLoading={false}
              isError={false}
              onEdit={(post) => navigate(`/posts/editor/${post.id}`)}
              onDelete={handleDelete}
              onComments={handleComments}
            />
          )}

          {data?.meta && (
            <div className="d-flex justify-content-center mt-4">
              <nav>
                <ul className="pagination">
                  <li className={`page-item ${page <= 1 ? 'disabled' : ''}`}>
                    <button
                      className="page-link"
                      onClick={() => setPage((p) => Math.max(p - 1, 1))}
                    >
                      Previous
                    </button>
                  </li>
                  <li className="page-item active">
                    <span className="page-link">
                      Page {data.meta.current_page} of {data.meta.last_page}
                    </span>
                  </li>
                  <li className={`page-item ${page >= (data.meta.last_page ?? 1) ? 'disabled' : ''}`}>
                    <button
                      className="page-link"
                      onClick={() => setPage((p) => p + 1)}
                    >
                      Next
                    </button>
                  </li>
                </ul>
              </nav>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}