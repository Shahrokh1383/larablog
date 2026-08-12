export interface SiteSettings {
  call_us_phone: string | null;
  call_us_emails: string[];
  visit_address: string | null;
  social_links: {
    linkedin?: string;
    github?: string;
    twitter?: string;
    instagram?: string;
    dribbble?: string;
    youtube?: string;
    discord?: string;
  };
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