export interface Author {
  id: string;
  name: string;
  username?: string;
  avatar?: string;
  bio?: string;
  social_links?: Record<string, string>;
}

export interface Category {
  id: string;
  name: string;
  slug: string;
}

export interface Tag {
  id: string;
  name: string;
  slug: string;
}

export interface Post {
  id: string;
  title: string;
  slug: string;
  body: string;
  excerpt?: string | null;
  featured_image?: string | null;
  reading_time: number;
  views: number;
  comments_count: number;
  is_saved: boolean;
  published_at: string;
  is_editors_pick: boolean;
  category?: Category | null;
  tags?: Tag[];
  author?: Author | null;
  created_at: string;
  updated_at: string;
}