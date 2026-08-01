export interface AdminUser {
  id: string;
  name: string;
  email: string;
  avatar: string | null;
  bio: string | null;
  roles: string[];
  created_at: string;
}

export interface UpdateRolePayload {
  role: string;
}

export interface UpdatePasswordPayload {
  password: string;
  password_confirmation: string;
}