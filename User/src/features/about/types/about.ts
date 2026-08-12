export interface SocialLinks {
  [key: string]: string | null;
}

export interface TeamMemberUser {
  id: string;
  name: string;
  email: string;
  avatar: string | null;
  bio: string | null;
  expertise: string | null;
  roles: string[];
  social_links: SocialLinks | null;
}

export interface TeamMember {
  id: string;
  user_id: string;
  sort_order: number;
  is_active: boolean;
  user: TeamMemberUser | null;
  created_at: string;
  updated_at: string;
}

export interface SiteSettings {
  call_us_phone: string | null;
  call_us_emails: string[] | null;
  visit_address: string | null;
  social_links: Record<string, string> | null;
  story_image: string | null;
}

export interface AboutData {
  settings: SiteSettings;
  team_members: TeamMember[];
  team_members_pagination: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}