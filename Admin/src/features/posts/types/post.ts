export interface PostUser {
  id: string;
  name: string;
}

export interface PostCategory {
  id: string;
  name: string;
  slug: string;
}

export interface PostTag {
  id: string;
  name: string;
  slug: string;
}

export interface Post {
  id: string;
  title: string;
  slug: string;
  body: string;
  excerpt: string | null;
  featured_image: string | null;
  is_published: boolean;
  published_at: string | null;
  reading_time: number | null;
  user: PostUser;
  category: PostCategory | null;
  tags: PostTag[];
  created_at: string;
  updated_at: string;
}

export interface PostFormData {
  title: string;
  body: string;
  excerpt?: string;
  featured_image?: string;
  is_published: boolean;
  category_id?: string;
  tag_ids?: string[];
}