interface CommentItemProps {
  author: string;
  date: string;
  text: string;
  onReply: (author: string) => void;
}

export default function CommentItem({ author, date, text, onReply }: CommentItemProps) {
  return (
    <li className="comment-item">
      <div className="comment-body">
        <div className="comment-avatar">
          <img src={`https://picsum.photos/seed/${author}/50/50`} alt={author} loading="lazy" />
        </div>
        <div className="comment-content">
          <div className="comment-header">
            <span className="comment-author">{author}</span>
            <span className="comment-date">{date}</span>
          </div>
          <p className="comment-text">{text}</p>
          <button className="comment-reply-btn" onClick={() => onReply(author)}>
            <i className="fa-sharp fa-solid fa-reply"></i> Reply
          </button>
        </div>
      </div>
    </li>
  );
}