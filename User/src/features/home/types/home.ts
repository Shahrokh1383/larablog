import { Post } from '@/features/posts/types/post';
import { Category } from '@/features/categories/types/category';

export interface HomeData {
  featured_posts: Post[];
  recent_posts: Post[];
  categories: Category[];
  total_posts_count: number;
}