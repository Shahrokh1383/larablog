export interface Tag {
  id: string;
  name: string;
  slug: string;
  posts_count: number;
  total_views?: number;
}

export interface TagFormData {
  name: string;
}