<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - {{ getAppName() }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ getFaviconUrl() }}" />
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .verify-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo img {
            max-height: 60px;
        }
        .verification-icon {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="verify-card p-5">
                    <div class="logo">
                        <a href="{{ route('home') }}">
                            <img src="{{ getAppLogo() }}" alt="{{ getAppName() }}">
                        </a>
                    </div>
                    
                    <div class="text-center">
                        <div class="verification-icon">📧</div>
                        <h2 class="mb-4">Verify Your Email</h2>
                        <p class="mb-4">Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.</p>
                        
                        @if (session('status') == 'verification-link-sent')
                            <div class="alert alert-success" role="alert">
                                A new verification link has been sent to your email address.
                            </div>
                        @endif

                        <form method="POST" action="{{ route('verification.resend') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                                       name="email" value="{{ old('email') }}" required autocomplete="email">
                                @error('email')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary">
                                    Resend Verification Email
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <p class="mb-0">Already verified? 
                                <a href="{{ route('login') }}" class="text-decoration-none fw-bold">Sign In</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
