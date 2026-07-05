<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login - GardenHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-family: Inter, Arial, sans-serif;
        }
        .auth-card {
            width: 100%;
            max-width: 460px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            box-shadow: 0 12px 40px rgba(15, 23, 42, 0.08);
            padding: 32px;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
            color: #111827;
            font-weight: 700;
            font-size: 1.35rem;
        }
        .brand i { color: #16a34a; }
        .muted { color: #6b7280; }
        .form-control {
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 11px 12px;
        }
        .form-control:focus {
            border-color: #16a34a;
            box-shadow: 0 0 0 0.2rem rgba(22, 163, 74, 0.15);
        }
        .btn-submit {
            background: #16a34a;
            border: none;
            border-radius: 10px;
            padding: 11px 14px;
            font-weight: 600;
        }
        .btn-submit:hover { background: #15803d; }
        .link-text { color: #16a34a; text-decoration: none; }
        .link-text:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="text-center mb-4">
        <div class="brand justify-content-center">
            <i class="fas fa-leaf"></i>
            <span>GardenHub Staff</span>
        </div>
        <p class="muted mb-0">Access your staff dashboard securely</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger mb-4">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('staff.login.post') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-semibold">Email or Phone</label>
            <input type="text" name="email" class="form-control" placeholder="Enter email or phone" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
        </div>
        <button type="submit" class="btn btn-submit text-white w-100">Login</button>
    </form>

    <div class="text-center mt-4">
        <a href="{{ route('staff.register') }}" class="link-text">Create a staff account</a>
    </div>
</div>
</body>
</html>
