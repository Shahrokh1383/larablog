import Link from 'next/link';
import { Author } from '../types/post';

interface AuthorBioCardProps {
  author: Author;
}

export default function AuthorBioCard({ author }: AuthorBioCardProps) {
  return (
    <div className="author-bio-card">
      <img 
        src={author.avatar || `https://picsum.photos/seed/${author.id}/80/80`} 
        alt={author.name} 
        className="author-bio-avatar"
      />
      <div className="author-bio-content">
        <h4><Link href={`/author/${author.username || author.id}`}>{author.name}</Link></h4>
        <p>{author.bio || 'Senior full-stack developer and open-source contributor.'}</p>
        <div className="author-social">
          <a href="#" aria-label="Twitter"><i className="fa-brands fa-x-twitter"></i></a>
          <a href="#" aria-label="GitHub"><i className="fa-brands fa-github"></i></a>
          <a href="#" aria-label="LinkedIn"><i className="fa-brands fa-linkedin-in"></i></a>
        </div>
      </div>
    </div>
  );
}