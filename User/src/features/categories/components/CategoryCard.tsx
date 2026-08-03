import Link from 'next/link';
import type { Category } from '../types/category';

const getCategoryIcon = (slug: string): string => {
  const s = slug.toLowerCase();
  if (s.includes('dev') || s.includes('code')) return 'fa-solid fa-code';
  if (s.includes('design')) return 'fa-solid fa-palette';
  if (s.includes('business')) return 'fa-solid fa-chart-line';
  if (s.includes('security')) return 'fa-solid fa-shield-halved';
  if (s.includes('ai') || s.includes('ml')) return 'fa-solid fa-robot';
  if (s.includes('mobile')) return 'fa-solid fa-mobile-screen';
  if (s.includes('cloud')) return 'fa-solid fa-cloud';
  return 'fa-solid fa-folder'; // Default fallback
};

interface CategoryCardProps {
  category: Category;
}

export default function CategoryCard({ category }: CategoryCardProps) {
  const iconClass = getCategoryIcon(category.slug);

  return (
    <div className="col-md-4 col-6 category-card-col" data-tag={category.slug}>
      <Link href={`/category/${category.slug}`} className="category-card">
        <div className="category-card-icon"><i className={iconClass}></i></div>
        <h4 className="category-card-name">{category.name}</h4>
        <span className="category-card-count">{category.posts_count} articles</span>
      </Link>
    </div>
  );
}