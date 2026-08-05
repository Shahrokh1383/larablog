import Link from 'next/link';
import type { Author } from '../types/author';
import type { Tag } from '@/features/tags/types/tag';

interface AuthorSidebarProps {
  author: Author;
  popularTags: Tag[] | undefined;
}

export default function AuthorSidebar({ author, popularTags }: AuthorSidebarProps) {
  return (
    <aside className="col-lg-4">
      <div className="sidebar">
        {/* About Author (compact) */}
        <div className="sidebar-card author-sidebar-card">
          <div className="text-center">
            <img 
              src={author.avatar || `https://picsum.photos/seed/${author.id}/100/100`} 
              alt={author.name} 
              className="author-sidebar-avatar"
            />
            <h4 className="author-sidebar-name">{author.name}</h4>
            <p className="author-sidebar-bio">{author.bio || 'Author at LaraBlog.'}</p>
            <Link href="/authors" className="btn btn-outline-custom btn-sm w-100">
              View All Authors
            </Link>
          </div>
        </div>

        {/* Popular Tags */}
        <div className="sidebar-card">
          <h4 className="sidebar-title">Popular Tags</h4>
          <div className="popular-tags-cloud">
            {popularTags?.map((tag, index) => (
              <Link 
                key={tag.id} 
                href={`/tags/${tag.slug}`}
                className="popular-tag-badge"
                style={{ fontSize: `${0.9 + (index % 4) * 0.15}rem` }}
              >
                {tag.name}
              </Link>
            ))}
          </div>
        </div>

        {/* Newsletter */}
        <div className="sidebar-card newsletter-sidebar">
          <h4 className="sidebar-title">Newsletter</h4>
          <p>Get the best articles delivered to your inbox.</p>
          <form className="newsletter-sidebar-form" onSubmit={(e) => e.preventDefault()}>
            <input type="email" className="form-control" placeholder="your@email.com" required />
            <button type="submit" className="btn btn-primary-custom w-100 mt-2">Subscribe</button>
          </form>
        </div>
      </div>
    </aside>
  );
}