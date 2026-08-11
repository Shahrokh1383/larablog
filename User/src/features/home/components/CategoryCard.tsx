import Link from 'next/link';
import { Category } from '@/features/categories/types/category';

interface CategoryCardProps {
  category?: Category;
  isMoreCard?: boolean;
  totalPostsCount?: number;
}

export function CategoryCard({ category, isMoreCard = false, totalPostsCount }: CategoryCardProps) {
  if (isMoreCard) {
    return (
      <Link href="/category" className="category-card">
        <div className="category-icon">
          <i className="fa-sharp fa-solid fa-ellipsis"></i>
        </div>
        <h4 className="category-name">More</h4>
        <span className="category-count">{totalPostsCount} articles</span>
      </Link>
    );
  }

  if (!category) return null;

  return (
    <Link href={`/category/${category.slug}`} className="category-card">
      <div className="category-icon">
        {/* In a real app, map icon dynamically based on category */}
        <i className="fas fa-solid fa-folder"></i> 
      </div>
      <h4 className="category-name">{category.name}</h4>
      <span className="category-count">{category.posts_count} articles</span>
    </Link>
  );
}