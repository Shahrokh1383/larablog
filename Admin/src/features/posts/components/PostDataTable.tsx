import type { Post } from '../types/post';

interface PostDataTableProps {
  posts: Post[];
  isLoading: boolean;
  isError: boolean;
  onEdit: (post: Post) => void;
  onDelete: (post: Post) => void;
}

export default function PostDataTable({
  posts,
  isLoading,
  isError,
  onEdit,
  onDelete,
}: PostDataTableProps) {
  if (isLoading) {
    return (
      <div className="text-center py-5">
        <div className="spinner-border" />
      </div>
    );
  }

  if (isError) {
    return <div className="alert alert-danger m-4">Failed to load posts.</div>;
  }

  if (posts.length === 0) {
    return (
      <div className="text-center py-5 text-muted">
        <i className="fas fa-newspaper fa-3x mb-3 d-block"></i>
        No posts found. Create your first post!
      </div>
    );
  }

  return (
    <div className="table-responsive">
      <table className="table table-hover align-middle mb-0">
        <thead className="table-light">
          <tr>
            <th>Title</th>
            <th>Author</th>
            <th>Category</th>
            <th>Status</th>
            <th>Created</th>
            <th className="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          {posts.map((post) => (
            <tr key={post.id}>
              <td className="fw-semibold">{post.title}</td>
              <td>{post.user?.name}</td>
              <td>{post.category?.name ?? '—'}</td>
              <td>
                {post.is_published ? (
                  <span className="badge bg-success">Published</span>
                ) : (
                  <span className="badge bg-warning text-dark">Draft</span>
                )}
              </td>
              <td>{new Date(post.created_at).toLocaleDateString()}</td>
              <td className="text-end">
                <button
                  className="btn btn-sm btn-outline-secondary me-1"
                  onClick={() => onEdit(post)}
                  title="Edit"
                >
                  <i className="fas fa-pen"></i>
                </button>
                <button
                  className="btn btn-sm btn-outline-danger"
                  onClick={() => onDelete(post)}
                  title="Delete"
                >
                  <i className="fas fa-trash"></i>
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}