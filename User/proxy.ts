import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

export function proxy(request: NextRequest) {
  const token = request.cookies.get('auth_token')?.value;
  const { pathname } = request.nextUrl;

  // Define public routes
  const publicRoutes = ['/', '/tags', '/category', '/api/categories', '/api/tags'];
  const isPublic = publicRoutes.some(route => pathname.startsWith(route) || pathname === route);

  // If trying to access dashboard without token, redirect to login
  if (pathname.startsWith('/dashboard') && !token) {
    return NextResponse.redirect(new URL('/login', request.url));
  }

  // If authenticated and trying to access login/register, redirect to dashboard
  if (token && (pathname.startsWith('/login') || pathname.startsWith('/register'))) {
    return NextResponse.redirect(new URL('/dashboard', request.url));
  }

  return NextResponse.next();
}

export const config = {
  matcher: ['/((?!api|_next/static|_next/image|favicon.ico).*)'],
};