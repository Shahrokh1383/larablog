import Link from 'next/link';
import type { Tag } from '../types/tag';

interface TagsSidebarProps {
  popularTags: Tag[];
}

export default function TagsSidebar({ popularTags }: TagsSidebarProps) {
  return (
    <aside className="col-lg-4">
      <div className="sidebar">
        <div className="sidebar-card">
          <h4 className="sidebar-title">Popular Tags</h4>
          <div className="popular-tags-cloud">
            {popularTags.map((tag, index) => (
              <Link 
                key={tag.id} 
                href={`/tags?search=${tag.slug}`} 
                className="popular-tag-badge"
                style={{ fontSize: `${0.9 + (index % 4) * 0.15}rem` }}
              >
                {tag.name}
              </Link>
            ))}
          </div>
        </div>

        <div className="sidebar-card">
          <h4 className="sidebar-title">Categories</h4>
          <ul className="categories-sidebar-list">
            <li><a href="#"><i className="fa-sharp fa-solid fa-code"></i> Development <span>45</span></a></li>
            <li><a href="#"><i className="fa-sharp fa-solid fa-palette"></i> Design <span>32</span></a></li>
            <li><a href="#"><i className="fa-sharp fa-solid fa-chart-line"></i> Business <span>28</span></a></li>
            <li><a href="#"><i className="fa-sharp fa-solid fa-shield-halved"></i> Security <span>19</span></a></li>
          </ul>
        </div>

        <div className="sidebar-card newsletter-sidebar">
          <h4 className="sidebar-title">Newsletter</h4>
          <p>Get the best articles delivered to your inbox.</p>
          <form className="newsletter-sidebar-form">
            <input type="email" className="form-control" placeholder="your@email.com" required />
            <button type="submit" className="btn btn-primary-custom w-100 mt-2">Subscribe</button>
          </form>
        </div>
      </div>
    </aside>
  );
}