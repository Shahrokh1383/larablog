export interface Category {
  id: string;
  name: string;
  slug: string;
  description?: string;
  posts_count: number;
  authors_count: number;
}