'use client';

interface SavePostButtonProps {
  isSaved: boolean;
  isLoading: boolean;
  onToggle: () => void;
}

export default function SavePostButton({ isSaved, isLoading, onToggle }: SavePostButtonProps) {
  return (
    <button 
      className={`btn-save-post ${isSaved ? 'is-saved' : ''}`}
      onClick={onToggle}
      disabled={isLoading}
      aria-label={isSaved ? 'Unsave Post' : 'Save Post'}
    >
      <i className={`fa-sharp fa-solid fa-bookmark`}></i>
      <span>{isSaved ? 'Saved' : 'Save'}</span>
    </button>
  );
}