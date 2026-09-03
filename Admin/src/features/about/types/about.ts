export interface SiteSettings {
  call_us_phone: string | null;
  call_us_emails: string[] | null;
  visit_address: string | null;
  social_links: {
    linkedin?: string | null;
    github?: string | null;
    twitter?: string | null;
    instagram?: string | null;
    dribbble?: string | null;
    youtube?: string | null;
    discord?: string | null;
  } | null;
  story_image: string | null;
}

export interface TeamMemberUser {
  id: string;
  name: string;
  email: string;
  avatar: string | null;
  bio: string | null;
  expertise: string | null;
  roles: string[];
  social_links: Record<string, string | null> | null;
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

export interface TeamMemberFormData {
  user_id: string;
  sort_order: number;
  is_active: boolean;
}

export interface EligibleUser {
  id: string;
  name: string;
  email: string;
  avatar: string | null;
  roles: string[];
}