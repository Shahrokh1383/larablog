export interface Author {
  id: string;
  name: string;
  username?: string;
  avatar?: string;
  bio?: string;
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
  excerpt: string;
  featured_image: string;
  reading_time: number;
  views: number;
  published_at: string | null;
  created_at: string;
  updated_at: string;
  category: Category;
  tags: Tag[];
  author: Author;
}