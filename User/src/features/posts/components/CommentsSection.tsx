'use client';

import { useState } from 'react';
import CommentItem from './CommentItem';
import CommentForm from './CommentForm';

const staticComments = [
  { author: 'Jane Cooper', date: 'March 6, 2024 at 10:24 am', text: 'Excellent article! The layer separation really makes sense. I\'ve been struggling with fat controllers, and this approach will definitely help me structure my next project.' },
  { author: 'Robert Fox', date: 'March 7, 2024 at 3:15 pm', text: 'One question: how do you handle Eloquent relationships inside the domain layer? I\'d love to see a follow-up on that.' },
  { author: 'Leslie Alexander', date: 'March 8, 2024 at 9:02 am', text: 'Thanks for this guide! I implemented the domain layer in my app and testing has become so much easier. The repository pattern is a game changer.' },
];

export default function CommentsSection() {
  const [replyTo, setReplyTo] = useState<string | null>(null);

  const handleReply = (author: string) => {
    setReplyTo(author);
    document.getElementById('commentFormWrapper')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
  };

  return (
    <section className="comments-section" id="comments">
      <div className="section-header">
        <h2 className="comments-title">Comments <span className="text-gradient">(3)</span></h2>
        <p className="comments-desc">Join the discussion and share your thoughts.</p>
      </div>

      <ul className="comments-list" id="commentsList">
        {staticComments.map((c, idx) => (
          <CommentItem key={idx} author={c.author} date={c.date} text={c.text} onReply={handleReply} />
        ))}
      </ul>

      <CommentForm replyTo={replyTo} onClearReply={() => setReplyTo(null)} />
    </section>
  );
}