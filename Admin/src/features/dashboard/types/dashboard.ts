export interface StatItem {
  id: string;
  name: string;
  slug: string;
  posts_count: number;
}

export interface DashboardStats {
  total_posts: number;
  published_posts: number;
  total_views: number;
  popular_categories: StatItem[];
  popular_tags: StatItem[];
}

export interface AuthorStats {
  posts_count: number;
  total_views: number;
}

export interface TopCommenter {
  user_id: string | null;
  name: string | null;
  email: string | null;
  comments_count: number;
}