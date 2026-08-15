export interface AuthUser {
  id: string;
  name: string;
  email: string;
  username: string;
  roles: string[];
  created_at: string;
  avatar?: string | null;
  bio?: string | null;
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

export interface LaravelValidationError {
  message: string;
  errors: Record<string, string[]>;
}