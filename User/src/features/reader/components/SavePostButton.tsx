'use client';

import { useToggleSavedPost } from '../hooks/useToggleSavedPost';

interface SavePostButtonProps {
  postId: string;
  isSaved: boolean;
}

export default function SavePostButton({ postId, isSaved }: SavePostButtonProps) {
  const toggleMutation = useToggleSavedPost(postId, isSaved);

  return (
    <button 
      className={`btn-save-post ${isSaved ? 'is-saved' : ''}`}
      onClick={() => toggleMutation.mutate()}
      disabled={toggleMutation.isPending}
      aria-label={isSaved ? 'Unsave Post' : 'Save Post'}
    >
      <i className={`fa-sharp fa-solid fa-bookmark`}></i>
      <span>{isSaved ? 'Saved' : 'Save'}</span>
    </button>
  );
}