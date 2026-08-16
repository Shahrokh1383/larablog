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
  return 'fa-solid fa-folder'; 
};

interface CategoryCardProps { category: Category; }

export default function CategoryCard({ category }: CategoryCardProps) {
  return (
    <Link href={`/category/${category.slug}`} className="taxonomy-card">
      <div className="taxonomy-card-icon"><i className={getCategoryIcon(category.slug)}></i></div>
      <h4 className="taxonomy-card-name">{category.name}</h4>
      <span className="taxonomy-card-count">{category.posts_count} articles</span>
    </Link>
  );
}