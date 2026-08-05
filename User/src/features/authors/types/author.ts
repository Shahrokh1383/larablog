export interface SocialLinks {
  twitter?: string;
  github?: string;
  linkedin?: string;
  instagram?: string;
  dribbble?: string;
  website?: string;
}

export interface Author {
  id: string;
  user_id: string;
  name: string;
  username: string;
  avatar: string | null;
  bio: string | null;
  expertise: string | null;
  years_of_experience: number | null;
  social_links: Record<string, string>;
  posts_count: number;
  total_views: number;
  created_at: string;
}