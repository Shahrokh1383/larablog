'use client';

import { useState, useEffect, useRef } from 'react';
import { useCreateComment } from '../hooks/useCreateComment';
import { useAuth } from '@/features/auth/context/AuthContext';

interface CommentFormProps {
  postId: string;
  replyTo: { id: string; name: string } | null;
  onClearReply: () => void;
}

export default function CommentForm({ postId, replyTo, onClearReply }: CommentFormProps) {
  const { user, isAuthenticated } = useAuth();
  const createComment = useCreateComment();
  
  const [validated, setValidated] = useState(false);
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [comment, setComment] = useState('');
  const textareaRef = useRef<HTMLTextAreaElement>(null);

  useEffect(() => {
    if (replyTo) {
      const mention = `@${replyTo.name} `;
      if (!comment.startsWith(mention)) {
        setComment(mention);
      }
      setTimeout(() => {
        if (textareaRef.current) {
          textareaRef.current.focus();
          textareaRef.current.selectionStart = textareaRef.current.selectionEnd = mention.length;
        }
      }, 10);
    }
  }, [replyTo, comment]);

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const form = e.currentTarget;
    if (!form.checkValidity()) {
      e.stopPropagation();
      setValidated(true);
      return;
    }
    
    try {
      await createComment.mutateAsync({
        post_id: postId,
        body: comment,
        parent_id: replyTo?.id || null,
        name: isAuthenticated ? undefined : name,
        email: isAuthenticated ? undefined : email,
      });
      
      setValidated(false);
      setName('');
      setEmail('');
      setComment('');
      onClearReply();
    } catch (error) {
      console.error('Failed to post comment', error);
    }
  };

  return (
    <div className="comment-form-wrapper" id="commentFormWrapper">
      <h3 className="comment-form-title">Leave a Reply</h3>
      {replyTo && (
        <p className="comment-form-note text-primary">
          Replying to <strong>@{replyTo.name}</strong>. 
          <button type="button" onClick={onClearReply} className="btn btn-link btn-sm p-0 ms-2">Cancel reply</button>
        </p>
      )}
      {!isAuthenticated && <p className="comment-form-note">Your email address will not be published. Required fields are marked *</p>}
      
      <form onSubmit={handleSubmit} className={`comment-form ${validated ? 'was-validated' : ''}`} noValidate>
        <div className="row g-3">
          {!isAuthenticated && (
            <>
              <div className="col-md-6">
                <label htmlFor="commentName" className="form-label">Name *</label>
                <input type="text" className="form-control" id="commentName" value={name} onChange={(e) => setName(e.target.value)} required />
                <div className="invalid-feedback">Please enter your name.</div>
              </div>
              <div className="col-md-6">
                <label htmlFor="commentEmail" className="form-label">Email *</label>
                <input type="email" className="form-control" id="commentEmail" value={email} onChange={(e) => setEmail(e.target.value)} required />
                <div className="invalid-feedback">Please enter a valid email address.</div>
              </div>
            </>
          )}
          <div className="col-12">
            <label htmlFor="commentText" className="form-label">Comment *</label>
            <textarea ref={textareaRef} className="form-control" id="commentText" rows={5} value={comment} onChange={(e) => setComment(e.target.value)} required></textarea>
            <div className="invalid-feedback">Please write a comment.</div>
          </div>
          <div className="col-12">
            <button type="submit" className="btn btn-primary-custom btn-lg" disabled={createComment.isPending}>
              <span>{createComment.isPending ? 'Posting...' : 'Post Comment'}</span>
              <i className="fa-sharp fa-solid fa-paper-plane ms-2"></i>
            </button>
          </div>
        </div>
      </form>
    </div>
  );
}