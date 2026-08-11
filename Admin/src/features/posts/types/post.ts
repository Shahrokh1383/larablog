export interface PostUser {
  id: string;
  name: string;
}

export interface PostCategory {
  id: string;
  name: string;
  slug: string;
  posts_count?: number;
}

export interface PostTag {
  id: string;
  name: string;
  slug: string;
  posts_count?: number;
}

export interface Post {
  id: string;
  title: string;
  slug: string;
  body: string;
  excerpt: string | null;
  featured_image: string | null;
  is_published: boolean;
  is_editors_pick: boolean;
  published_at: string | null;
  reading_time: number | null;
  views: number;
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
  is_editors_pick: boolean;
  category_id?: string;
  tag_ids?: string[];
}