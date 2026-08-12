import { Post } from '@/features/posts/types/post';
import { Category } from '@/features/categories/types/category';
import { Tag } from '@/features/tags/types/tag';
import { Author } from '@/features/posts/types/post';

export interface PaginatedPosts {
  data: Post[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export interface SearchResponse {
  posts: PaginatedPosts;
  categories: Category[];
  tags: Tag[];
  authors: Author[];
}