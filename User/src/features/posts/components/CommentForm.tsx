'use client';

import { useState } from 'react';

interface CommentFormProps {
  replyTo: string | null;
  onClearReply: () => void;
}

export default function CommentForm({ replyTo, onClearReply }: CommentFormProps) {
  const [validated, setValidated] = useState(false);
  const [name, setName] = useState('');
  const [comment, setComment] = useState(replyTo ? `@${replyTo} ` : '');

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const form = e.currentTarget;
    if (!form.checkValidity()) {
      e.stopPropagation();
      setValidated(true);
      return;
    }
    
    // Simulate submission
    setValidated(false);
    setName('');
    setComment('');
    onClearReply();
    alert('Comment submitted successfully (simulated).');
  };

  return (
    <div className="comment-form-wrapper" id="commentFormWrapper">
      <h3 className="comment-form-title">Leave a Reply</h3>
      {replyTo && (
        <p className="comment-form-note text-primary">
          Replying to <strong>@{replyTo}</strong>. <button onClick={onClearReply} className="btn btn-link btn-sm p-0">Cancel reply</button>
        </p>
      )}
      <p className="comment-form-note">Your email address will not be published. Required fields are marked *</p>
      <form onSubmit={handleSubmit} className={`comment-form ${validated ? 'was-validated' : ''}`} noValidate>
        <div className="row g-3">
          <div className="col-md-6">
            <label htmlFor="commentName" className="form-label">Name *</label>
            <input 
              type="text" 
              className="form-control" 
              id="commentName" 
              value={name}
              onChange={(e) => setName(e.target.value)}
              required 
              placeholder="Your name"
            />
            <div className="invalid-feedback">Please enter your name.</div>
          </div>
          <div className="col-md-6">
            <label htmlFor="commentEmail" className="form-label">Email *</label>
            <input type="email" className="form-control" id="commentEmail" required placeholder="your@email.com" />
            <div className="invalid-feedback">Please enter a valid email address.</div>
          </div>
          <div className="col-12">
            <label htmlFor="commentWebsite" className="form-label">Website</label>
            <input type="url" className="form-control" id="commentWebsite" placeholder="https://example.com" />
          </div>
          <div className="col-12">
            <label htmlFor="commentText" className="form-label">Comment *</label>
            <textarea 
              className="form-control" 
              id="commentText" 
              rows={5} 
              value={comment}
              onChange={(e) => setComment(e.target.value)}
              required 
              placeholder="Write your comment here..."
            ></textarea>
            <div className="invalid-feedback">Please write a comment.</div>
          </div>
          <div className="col-12">
            <button type="submit" className="btn btn-primary-custom btn-lg" id="commentSubmitBtn">
              <span>Post Comment</span>
              <i className="fa-sharp fa-solid fa-paper-plane"></i>
            </button>
          </div>
        </div>
      </form>
    </div>
  );
}