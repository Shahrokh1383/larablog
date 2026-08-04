export interface Tag {
  id: string;
  name: string;
  slug: string;
  posts_count: number;
  created_at: string;
  updated_at: string;
}

export interface TagFormData {
  name: string;
}