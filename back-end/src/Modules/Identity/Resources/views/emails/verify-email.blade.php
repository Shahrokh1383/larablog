<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Verify Your Email</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f9fafb; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #ffffff; }
        .button { display: inline-block; padding: 12px 24px; background: #3b82f6; color: #ffffff; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome, {{ $user->name }}!</h1>
        <p>Please verify your email address by clicking the button below:</p>
        <a href="{{ $verificationUrl }}" class="button">Verify Email Address</a>
        <p>If you did not create an account, no further action is required.</p>
    </div>
</body>
</html>