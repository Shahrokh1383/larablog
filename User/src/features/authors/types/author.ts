export interface Author {
  id: string;
  name: string;
  username: string;
  avatar: string | null;
  bio: string | null;
  expertise: string | null;
  social_links: Record<string, string>;
  posts_count: number;
  total_views: number;
}