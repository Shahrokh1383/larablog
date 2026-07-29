<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reset Your Password</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f9fafb; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #ffffff; }
        .button { display: inline-block; padding: 12px 24px; background: #3b82f6; color: #ffffff; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Hello, {{ $user->name }}!</h1>
        <p>You are receiving this email because we received a password reset request for your account.</p>
        <a href="{{ $resetUrl }}" class="button">Reset Password</a>
        <p>If you did not request a password reset, no further action is required.</p>
    </div>
</body>
</html>