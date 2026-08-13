export const endpoints = {
  auth: {
    login: '/login',
    register: '/register',
    logout: '/logout',
    user: '/user',
    forgotPassword: '/forgot-password',
    resetPassword: '/reset-password',
    oauthRedirect: (provider: string) => `/oauth/${provider}/redirect`,
    oauthCallback: (provider: string) => `/oauth/${provider}/callback`,
    emailVerify: (id: string, hash: string) => `/email/verify/${id}/${hash}`,
  },
  content: {
    categories: '/categories',
    categoryPosts: (slug: string) => `/categories/${slug}/posts`,
    tags: '/tags',
    popularTags: '/tags/popular',
    tagPosts: (slug: string) => `/tags/${slug}/posts`,
  },
  about: {
    index: '/about',
  },
};