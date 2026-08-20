import type { Category } from '../types/category';

interface CategoryDataTableProps {
  categories: Category[];
  isLoading: boolean;
  isError: boolean;
  canEdit: boolean;
  canDelete: boolean;
  onEdit: (cat: Category) => void;
  onDelete: (cat: Category) => void;
}

export default function CategoryDataTable({
  categories,
  isLoading,
  isError,
  canEdit,
  canDelete,
  onEdit,
  onDelete,
}: CategoryDataTableProps) {
  if (isLoading) {
    return (
      <div className="text-center py-5">
        <div className="spinner-border" />
      </div>
    );
  }

  if (isError) {
    return <div className="alert alert-danger m-4">Failed to load categories.</div>;
  }

  if (categories.length === 0) {
    return (
      <div className="text-center py-5 text-muted">
        <i className="fas fa-folder fa-3x mb-3 d-block"></i>
        No categories yet.
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
          {categories.map((cat) => (
            <tr key={cat.id}>
              <td className="fw-semibold">{cat.name}</td>
              <td><code>{cat.slug}</code></td>
              <td>
                <span className="badge bg-info text-dark">{cat.posts_count ?? 0}</span>
              </td>
              <td>{new Date(cat.created_at).toLocaleDateString()}</td>
              {(canEdit || canDelete) && (
                <td className="text-end">
                  {canEdit && (
                    <button
                      className="btn btn-sm btn-outline-secondary me-1"
                      onClick={() => onEdit(cat)}
                      title="Edit category"
                    >
                      <i className="fas fa-pen"></i>
                    </button>
                  )}
                  {canDelete && (
                    <button
                      className="btn btn-sm btn-outline-danger"
                      onClick={() => onDelete(cat)}
                      title="Delete category"
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
