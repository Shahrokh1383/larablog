import PostForm from './PostForm';
import type { PostFormData } from '../types/post';
import type { Category } from '@/features/categories/types/category';
import type { Tag } from '@/features/tags/types/tag';

interface PostFormModalProps {
  show: boolean;
  title: string;
  initialData?: Partial<PostFormData>;
  categories: Category[];
  tags: Tag[];
  onSubmit: (data: PostFormData) => void;
  isLoading: boolean;
  serverError: string | null;
  onClose: () => void;
}

export default function PostFormModal({
  show,
  title,
  initialData,
  categories,
  tags,
  onSubmit,
  isLoading,
  serverError,
  onClose,
}: PostFormModalProps) {
  if (!show) return null;

  return (
    <div className="modal d-block" tabIndex={-1} style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
      <div className="modal-dialog modal-lg modal-dialog-scrollable">
        <div className="modal-content">
          <div className="modal-header">
            <h5 className="modal-title">{title}</h5>
            <button type="button" className="btn-close" onClick={onClose}></button>
          </div>
          <div className="modal-body">
            <PostForm
              initialData={initialData}
              categories={categories}
              tags={tags}
              onSubmit={(data) => {
                onSubmit(data);
                onClose();
              }}
              isLoading={isLoading}
              serverError={serverError}
            />
          </div>
        </div>
      </div>
    </div>
  );
}