<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { background: #2563eb; color: #ffffff; padding: 24px; text-align: center; }
        .content { padding: 24px; }
        .original { background: #f9fafb; padding: 16px; border-radius: 4px; margin-top: 24px; border-left: 4px solid #cbd5e1; color: #6b7280; font-size: 14px; }
        .reply-box { background: #ffffff; padding: 16px; border: 1px solid #e2e8f0; border-radius: 4px; white-space: pre-wrap; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1>Reply from LaraBlog Support</h1></div>
        <div class="content">
            <p>Hello {{ $originalMessage->name }},</p>
            <p>We have received your message and here is our response:</p>
            <div class="reply-box">{{ $replyBody }}</div>
            <div class="original">
                <strong>Your original message ({{ $originalMessage->subject }}):</strong><br>
                {{ $originalMessage->message }}
            </div>
        </div>
    </div>
</body>
</html>