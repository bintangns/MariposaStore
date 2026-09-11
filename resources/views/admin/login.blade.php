<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Project Mariposa</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #0a0a0f; color: #e2e8f0; font-family: 'Inter', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem; }
        .login-card { width: 100%; max-width: 22rem; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07); border-radius: 1rem; padding: 2rem; }
        .logo { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.75rem; }
        .logo img { height: 2.25rem; width: auto; border-radius: 0.375rem; }
        h1 { font-size: 1.125rem; font-weight: 600; color: white; }
        label { display: block; font-size: 0.75rem; color: #94a3b8; margin-bottom: 0.375rem; }
        input[type=email], input[type=password] {
            width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 0.5rem; padding: 0.625rem 0.875rem; color: white; font-size: 0.875rem;
            font-family: inherit; outline: none; margin-bottom: 1rem;
        }
        input[type=email]:focus, input[type=password]:focus { border-color: #7c3aed; }
        .btn-primary {
            width: 100%; background: linear-gradient(135deg, #7c3aed, #6d28d9); color: white;
            padding: 0.625rem 1.5rem; border-radius: 0.5rem; font-weight: 500; font-size: 0.875rem;
            border: none; cursor: pointer; transition: opacity 0.2s;
        }
        .btn-primary:hover { opacity: 0.9; }
        .error-box { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #f87171; padding: 0.625rem 0.875rem; border-radius: 0.5rem; font-size: 0.8125rem; margin-bottom: 1rem; }
        .success-box { background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.3); color: #4ade80; padding: 0.625rem 0.875rem; border-radius: 0.5rem; font-size: 0.8125rem; margin-bottom: 1rem; }
        .back-link { display: block; text-align: center; margin-top: 1.25rem; color: #64748b; font-size: 0.8125rem; text-decoration: none; }
        .back-link:hover { color: #94a3b8; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo"><img src="{{ asset('images/logo.png') }}" alt="Mariposa"><h1>Admin Login</h1></div>

        @if(session('success'))
            <div class="success-box">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="error-box">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">

            <button type="submit" class="btn-primary">Masuk</button>
        </form>

        <a href="{{ route('home') }}" class="back-link">← Kembali ke Website</a>
    </div>
</body>
</html>
