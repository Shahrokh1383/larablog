export interface SocialLinks {
  twitter?: string;
  github?: string;
  linkedin?: string;
  instagram?: string;
  dribbble?: string;
}

export interface Profile {
  id: string;
  user_id: string;
  name: string;
  username: string;
  avatar: string | null;
  bio: string | null;
  expertise: string | null;
  years_of_experience: number | null;
  social_links: SocialLinks;
}

export interface UpdateProfilePayload {
  name: string;
  avatar?: string | null;
  bio?: string | null;
  expertise?: string | null;
  years_of_experience?: number | null;
  social_links?: SocialLinks | null;
}