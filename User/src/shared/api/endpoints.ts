export const endpoints = {
  auth: {
    login: '/login',
    register: '/register',
    logout: '/logout',
    user: '/user',
    forgotPassword: '/forgot-password',
    resetPassword: '/reset-password',
    oauthRedirect: (provider: string) => `/auth/oauth/${provider}/redirect`,
    oauthCallback: (provider: string) => `/auth/oauth/${provider}/callback`,
    emailVerify: (id: string, hash: string) => `/email/verify/${id}/${hash}`,
  },
};