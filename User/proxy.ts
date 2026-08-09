import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

export function proxy(request: NextRequest) {
  const allCookies = request.cookies.getAll();
  const sessionCookie = allCookies.find(c => c.name.endsWith('-session'))?.value;
  
  const { pathname } = request.nextUrl;

  // If trying to access dashboard without ANY session cookie, redirect to login
  // (Guests with session cookies will be handled by the client-side layout)
  if (pathname.startsWith('/dashboard') && !sessionCookie) {
    return NextResponse.redirect(new URL('/login', request.url));
  }

  return NextResponse.next();
}

export const config = {
  matcher: ['/((?!api|_next/static|_next/image|favicon.ico).*)'],
};