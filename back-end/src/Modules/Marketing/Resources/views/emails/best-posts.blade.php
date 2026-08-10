<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { background: #2563eb; color: #ffffff; padding: 24px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 24px; }
        .post-item { border-bottom: 1px solid #eee; padding: 16px 0; }
        .post-item:last-child { border-bottom: none; }
        .post-title { font-size: 18px; color: #1f2937; text-decoration: none; font-weight: bold; display: block; margin-bottom: 8px; }
        .post-excerpt { color: #6b7280; font-size: 14px; margin: 0 0 12px 0; }
        .read-more { color: #2563eb; text-decoration: none; font-weight: bold; font-size: 14px; }
        .footer { text-align: center; padding: 16px; font-size: 12px; color: #9ca3af; background: #f9fafb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Top Posts of the Week</h1>
        </div>
        <div class="content">
            <p>Hello Reader,</p>
            <p>Here are the most popular articles on Larablog this week:</p>
            
            @foreach($posts as $post)
                <div class="post-item">
                    <a href="{{ config('app.frontend_url') }}/post/{{ $post->slug }}" class="post-title">
                        {{ $post->title }}
                    </a>
                    @if($post->excerpt)
                        <p class="post-excerpt">{{ Str::limit($post->excerpt, 120) }}</p>
                    @endif
                    <a href="{{ config('app.frontend_url') }}/post/{{ $post->slug }}" class="read-more">Read More →</a>
                </div>
            @endforeach
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Larablog. All rights reserved.</p>
            <p>You are receiving this because you subscribed to our newsletter.</p>
        </div>
    </div>
</body>
</html>