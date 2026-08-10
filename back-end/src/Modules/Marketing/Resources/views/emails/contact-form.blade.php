<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { background: #dc2626; color: #ffffff; padding: 24px; text-align: center; }
        .content { padding: 24px; }
        .detail { background: #f9fafb; padding: 16px; border-radius: 4px; margin-bottom: 16px; border-left: 4px solid #dc2626; }
        .label { font-weight: bold; color: #4b5563; display: block; margin-bottom: 4px; font-size: 12px; text-transform: uppercase; }
        .value { color: #111827; font-size: 16px; }
        .message-box { background: #f3f4f6; padding: 16px; border-radius: 4px; white-space: pre-wrap; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New Contact Message</h1>
        </div>
        <div class="content">
            <p>A new message has been submitted via the contact form:</p>
            
            <div class="detail">
                <span class="label">From Name</span>
                <span class="value">{{ $contactMessage->name }}</span>
            </div>
            <div class="detail">
                <span class="label">Email Address</span>
                <span class="value">{{ $contactMessage->email }}</span>
            </div>
            <div class="detail">
                <span class="label">Subject</span>
                <span class="value">{{ $contactMessage->subject }}</span>
            </div>
            
            <p><strong>Message:</strong></p>
            <div class="message-box">{{ $contactMessage->message }}</div>
        </div>
    </div>
</body>
</html>