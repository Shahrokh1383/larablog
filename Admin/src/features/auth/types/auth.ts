export interface AuthUser {
  id: string;
  name: string;
  email: string;
  avatar: string | null;
  bio: string | null;
  roles: string[];
  created_at: string;
}

export interface LoginCredentials {
  email: string;
  password: string;
  remember?: boolean;
}

export interface LoginResponse {
  user: AuthUser;
  token: string;
}