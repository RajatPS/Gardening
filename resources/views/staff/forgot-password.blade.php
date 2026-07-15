<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="mb-3">Forgot Password</h3>
                    <p class="text-muted">Enter your email and phone number to receive a verification code.</p>
                    @if(session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif
                    <form method="POST" action="{{ route('staff.forgot-password.post') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="e.g. 9876543210">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Country Code</label>
                            <input type="text" name="country_code" class="form-control" placeholder="e.g. 91">
                        </div>
                        <button type="submit" class="btn btn-primary">Send Verification Code</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
