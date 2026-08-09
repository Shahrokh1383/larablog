import Link from 'next/link';
import { Category } from '../types/post';

interface PostBreadcrumbProps {
  category: Category | null;
  title: string;
}

export default function PostBreadcrumb({ category, title }: PostBreadcrumbProps) {
  return (
    <nav aria-label="breadcrumb" className="post-breadcrumb">
      <ol className="breadcrumb">
        <li className="breadcrumb-item"><Link href="/">Home</Link></li>
        
        {category && (
          <li className="breadcrumb-item">
            <Link href={`/category/${category.slug}`}>{category.name}</Link>
          </li>
        )}
        
        <li className="breadcrumb-item active" aria-current="page">{title}</li>
      </ol>
    </nav>
  );
}