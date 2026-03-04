<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Inventory System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --green: #03624C; --teal: #0fb9b1; }
        body {
            background: linear-gradient(135deg, #e9fffa 0%, #f7faf9 100%);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 100%;
            animation: slideUp 0.5s ease;
        }
        .logo { text-align: center; margin-bottom: 30px; }
        .logo i { font-size: 48px; color: var(--green); }
        .logo h2 { color: var(--green); font-weight: 600; margin: 10px 0; }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            padding: 12px 15px;
            transition: all 0.3s;
        }
        .form-control:focus { border-color: var(--green); box-shadow: 0 0 0 0.2rem rgba(3,98,76,0.25); }
        .input-group-text { background: #f8f9fa; border: 2px solid #e0e0e0; border-right: none; color: #666; }
        .btn-login {
            background: linear-gradient(135deg, var(--green), var(--teal));
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            width: 100%;
            transition: transform 0.2s;
        }
        .btn-login:hover { transform: translateY(-2px); }
        .password-toggle { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #666; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: none; } }
        @media (max-width: 576px) { .login-card { padding: 30px 20px; } }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">
            <i class="bi bi-box-seam"></i>
            <h2>Inventory System</h2>
        </div>

        @if($errors->any())
            <div class="alert alert-danger border-0 rounded-3">
                @foreach($errors->all() as $error) {{ $error }}<br> @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                </div>
            </div>
            <div class="mb-3 position-relative">
                <label class="form-label fw-semibold">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <button type="button" class="password-toggle" onclick="togglePassword()"><i class="bi bi-eye"></i></button>
                </div>
            </div>
            <button type="submit" class="btn btn-login text-white"><i class="bi bi-box-arrow-in-right me-2"></i>Sign In</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = event.target;
            input.type = input.type === 'password' ? 'text' : 'password';
            icon.className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
        }
    </script>
</body>
</html>