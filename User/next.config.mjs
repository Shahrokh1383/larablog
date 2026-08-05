/** @type {import('next').NextConfig} */
const nextConfig = {
  reactCompiler: true,
  async rewrites() {
    return [
      {
        source: '/api/:path*',
        destination: 'http://localhost:8000/api/:path*',
      },
      {
        source: '/sanctum/:path*',
        destination: 'http://localhost:8000/sanctum/:path*',
      },
      {
        source: '/broadcasting/:path*',
        destination: 'http://localhost:8000/broadcasting/:path*',
      },
    ];
  },
};

export default nextConfig;