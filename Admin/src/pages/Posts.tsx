import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { usePosts, usePostMutations, PostDataTable } from '@/features/posts';
import type { Post } from '@/features/posts';

export default function PostsPage() {
  const navigate = useNavigate();
  const [search, setSearch] = useState('');
  const [debouncedSearch, setDebouncedSearch] = useState('');
  
  useEffect(() => {
    const timer = setTimeout(() => setDebouncedSearch(search), 500);
    return () => clearTimeout(timer);
  }, [search]);

  const { data: posts = [], isLoading, isError } = usePosts(debouncedSearch);
  const { deletePost } = usePostMutations();

  const handleDelete = (post: Post) => {
    if (window.confirm(`Delete "${post.title}"?`)) {
      deletePost.mutate(post.id);
    }
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
        <div className="card-header bg-white">
          <div className="input-group">
            <span className="input-group-text bg-transparent border-end-0">
              <i className="fas fa-search text-muted"></i>
            </span>
            <input
              type="text"
              className="form-control border-start-0"
              placeholder="Search posts by title or excerpt..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
            />
          </div>
        </div>
        <div className="card-body">
          <PostDataTable
            posts={posts}
            isLoading={isLoading}
            isError={isError}
            onEdit={(post) => navigate(`/posts/editor/${post.id}`)}
            onDelete={handleDelete}
          />
        </div>
      </div>
    </div>
  );
}