export interface Category {
  id: string;
  name: string;
  slug: string;
  posts_count: number;
  created_at: string;
  updated_at: string;
}

export interface CategoryFormData {
  name: string;
}