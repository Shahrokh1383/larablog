import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

export function proxy(request: NextRequest) {
  // Check for the Sanctum session cookie, NOT the admin auth_token
  const sessionCookie = request.cookies.get('laravel_session')?.value;
  const { pathname } = request.nextUrl;

  // Define public routes
  const publicRoutes = ['/', '/tags', '/category', '/api/categories', '/api/tags'];
  const isPublic = publicRoutes.some(route => pathname.startsWith(route) || pathname === route);

  // If trying to access dashboard without session, redirect to login
  if (pathname.startsWith('/dashboard') && !sessionCookie) {
    return NextResponse.redirect(new URL('/login', request.url));
  }

  // If already authenticated and trying to access login/register, redirect to dashboard
  if (sessionCookie && (pathname.startsWith('/login') || pathname.startsWith('/register'))) {
    return NextResponse.redirect(new URL('/dashboard', request.url));
  }

  return NextResponse.next();
}

export const config = {
  matcher: ['/((?!api|_next/static|_next/image|favicon.ico).*)'],
};