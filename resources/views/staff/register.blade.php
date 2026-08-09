<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Sign Up - GardenHub</title>
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
            max-width: 520px;
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
            justify-content: center;
        }
        .brand i { color: #16a34a; }
        .muted { color: #6b7280; }
        .form-group { margin-bottom: 16px; }
        .form-control {
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 11px 12px;
        }
        .form-control:focus {
            border-color: #16a34a;
            box-shadow: 0 0 0 0.2rem rgba(22, 163, 74, 0.15);
        }
        .btn-register {
            width: 100%;
            padding: 12px;
            background: #16a34a;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-register:hover { background: #15803d; }
        .register-footer { text-align: center; margin-top: 16px; }
        .register-footer a { color: #16a34a; text-decoration: none; }
        .register-footer a:hover { text-decoration: underline; }
        .alert { margin-bottom: 16px; }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="text-center mb-4">
            <div class="brand">
                <i class="fas fa-leaf"></i>
                <span>GardenHub Staff</span>
            </div>
            <p class="muted mb-0">Create your staff account</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <strong>Error!</strong> {{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('staff.register.post') }}">
            @csrf

            <div class="form-group">
                <label class="form-label fw-semibold">Full Name</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label fw-semibold">Email Address</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label fw-semibold">Phone Number</label>
                <div class="input-group">
                    <select class="form-select" name="country_code" id="countryCode" style="max-width: 140px;">
                        <option value="+91" data-flag="🇮🇳">🇮🇳 +91</option>
                        <option value="+1" data-flag="🇺🇸">🇺🇸 +1</option>
                        <option value="+44" data-flag="🇬🇧">🇬🇧 +44</option>
                        <option value="+61" data-flag="🇦🇺">🇦🇺 +61</option>
                        <option value="+971" data-flag="🇦🇪">🇦🇪 +971</option>
                        <option value="+81" data-flag="🇯🇵">🇯🇵 +81</option>
                        <option value="+65" data-flag="🇸🇬">🇸🇬 +65</option>
                        <option value="+49" data-flag="🇩🇪">🇩🇪 +49</option>
                    </select>
                    <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}" placeholder="Enter your number" required>
                    <button type="button" class="btn btn-outline-success" id="sendOtpBtn">Send OTP</button>
                </div>
                @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label fw-semibold">OTP</label>
                <input type="text" class="form-control @error('otp') is-invalid @enderror" name="otp" value="{{ old('otp') }}" required>
                @error('otp')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label fw-semibold">Address</label>
                <textarea class="form-control @error('address') is-invalid @enderror" name="address" rows="3">{{ old('address') }}</textarea>
                @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label fw-semibold">City</label>
                <input type="text" class="form-control @error('city') is-invalid @enderror" name="city" value="{{ old('city') }}">
                @error('city')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label fw-semibold">Password</label>
                <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" required>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label fw-semibold">Confirm Password</label>
                <input type="password" class="form-control" name="password_confirmation" required>
            </div>

            <button type="submit" class="btn-register">Create Account</button>

            <div class="register-footer">
                <a href="{{ route('staff.login') }}">Already have a staff account? Login</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('sendOtpBtn')?.addEventListener('click', function () {
            const phoneInput = document.querySelector('input[name="phone"]');
            const countryCode = document.getElementById('countryCode');
            const button = this;
            if (!phoneInput.value.trim()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Phone required',
                    text: 'Please enter your phone number first.',
                    confirmButtonColor: '#16a34a'
                });
                return;
            }

            button.disabled = true;
            button.textContent = 'Sending...';

            fetch('{{ route('staff.send-otp') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    phone: phoneInput.value.trim(),
                    country_code: countryCode.value
                })
            })
            .then(async response => {
                const text = await response.text();
                let data = {};

                try {
                    data = JSON.parse(text);
                } catch {
                    data = { message: text || 'Unable to send OTP' };
                }

                if (!response.ok) {
                    throw new Error(data.message || 'Unable to send OTP');
                }

                return data;
            })
            .then(data => {
                Swal.fire({
                    icon: 'success',
                    title: 'OTP sent',
                    text: data.message || 'A one-time password has been sent to your phone.',
                    confirmButtonColor: '#16a34a'
                });
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'OTP not sent',
                    text: error.message || 'Unable to send OTP right now.',
                    confirmButtonColor: '#16a34a'
                });
            })
            .finally(() => {
                button.disabled = false;
                button.textContent = 'Send OTP';
            });
        });
    </script>
</body>
</html>
