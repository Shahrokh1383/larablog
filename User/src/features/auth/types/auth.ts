export interface User {
  id: string;
  name: string;
  email: string;
  username: string;
  roles: string[];
  created_at: string;
  avatar?: string;
  bio?: string;
}

export interface LoginCredentials {
  email: string;
  password: string;
  remember?: boolean;
}

export interface RegisterCredentials {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export interface ForgotPasswordData {
  email: string;
}

export interface ResetPasswordData {
  token: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export interface AuthResponse {
  user: User;
  token?: string;
}

// Added for strict type safety across the module
export interface LaravelValidationError {
  message: string;
  errors: Record<string, string[]>;
}