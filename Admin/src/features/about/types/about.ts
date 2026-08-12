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
  };
}

export interface TeamMember {
  id: string;
  user_id: string;
  display_name: string | null;
  position: string;
  bio: string | null;
  photo: string | null;
  sort_order: number;
  is_active: boolean;
  user?: {
    id: string;
    name: string;
    email: string;
    avatar: string | null;
  };
  created_at: string;
  updated_at: string;
}

export interface TeamMemberFormData {
  user_id: string;
  display_name?: string;
  position: string;
  bio?: string;
  photo?: File | null;
  sort_order: number;
  is_active: boolean;
}