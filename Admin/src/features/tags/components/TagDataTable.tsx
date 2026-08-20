import type { Tag } from '../types/tag';

interface TagDataTableProps {
  tags: Tag[];
  isLoading: boolean;
  isError: boolean;
  canEdit: boolean;
  canDelete: boolean;
  onEdit: (tag: Tag) => void;
  onDelete: (tag: Tag) => void;
}

export default function TagDataTable({
  tags,
  isLoading,
  isError,
  canEdit,
  canDelete,
  onEdit,
  onDelete,
}: TagDataTableProps) {
  if (isLoading) {
    return (
      <div className="text-center py-5">
        <div className="spinner-border" />
      </div>
    );
  }

  if (isError) {
    return <div className="alert alert-danger m-4">Failed to load tags.</div>;
  }

  if (tags.length === 0) {
    return (
      <div className="text-center py-5 text-muted">
        <i className="fas fa-tags fa-3x mb-3 d-block"></i>
        No tags yet.
      </div>
    );
  }

  return (
    <div className="table-responsive">
      <table className="table table-hover align-middle mb-0">
        <thead className="table-light">
          <tr>
            <th>Name</th>
            <th>Slug</th>
            <th>Posts</th>
            <th>Created</th>
            {(canEdit || canDelete) && <th className="text-end">Actions</th>}
          </tr>
        </thead>
        <tbody>
          {tags.map((tag) => (
            <tr key={tag.id}>
              <td><span className="badge bg-secondary">{tag.name}</span></td>
              <td><code>{tag.slug}</code></td>
              <td>
                <span className="badge bg-info text-dark">{tag.posts_count ?? 0}</span>
              </td>
              <td>{new Date(tag.created_at).toLocaleDateString()}</td>
              {(canEdit || canDelete) && (
                <td className="text-end">
                  {canEdit && (
                    <button
                      className="btn btn-sm btn-outline-secondary me-1"
                      onClick={() => onEdit(tag)}
                      title="Edit tag"
                    >
                      <i className="fas fa-pen"></i>
                    </button>
                  )}
                  {canDelete && (
                    <button
                      className="btn btn-sm btn-outline-danger"
                      onClick={() => onDelete(tag)}
                      title="Delete tag"
                    >
                      <i className="fas fa-trash"></i>
                    </button>
                  )}
                </td>
              )}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}