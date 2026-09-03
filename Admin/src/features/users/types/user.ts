export interface AdminUser {
  id: string;
  name: string;
  email: string;
  username: string;
  roles: string[];
  avatar: string | null;
  created_at: string;
}

export interface UpdateRolePayload {
  role: string;
}

export interface UpdatePasswordPayload {
  password: string;
  password_confirmation: string;
}