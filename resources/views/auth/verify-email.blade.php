<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Verify Email - EcoConnect</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --denr-green: #1a472a;
            --denr-light-green: #2e7d32;
            --denr-gold: #d4af37;
            --denr-light: #f5f9f7;
            --denr-white: #ffffff;
            --denr-gray: #6b7280;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Open Sans', sans-serif;
            color: #333;
            background-color: var(--denr-light);
            line-height: 1.6;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Montserrat', sans-serif;
            color: var(--denr-green);
            margin-bottom: 1rem;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: var(--denr-green);
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            background-color: var(--denr-light-green);
            transform: translateY(-2px);
        }
        
        .btn-outline {
            background-color: transparent;
            border: 2px solid var(--denr-green);
            color: var(--denr-green);
        }
        
        .btn-outline:hover {
            background-color: var(--denr-green);
            color: white;
        }
        
        .section {
            padding: 80px 0;
        }
        
        .section-light {
            background-color: var(--denr-white);
        }
        
        .section-green {
            background-color: var(--denr-green);
            color: white;
        }
        
        .section-green h2, .section-green h3 {
            color: white;
        }
        
        .text-center {
            text-align: center;
        }
        
        .hero {
            background: linear-gradient(rgba(26, 71, 42, 0.8), rgba(26, 71, 42, 0.9)), url('/images/hero-bg.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 150px 0 100px;
            text-align: center;
        }
        
        .hero h1 {
            color: white;
            font-size: 3rem;
            margin-bottom: 1.5rem;
        }
        
        .hero p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto 2rem;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .feature-icon {
            font-size: 3rem;
            color: var(--denr-green);
            margin-bottom: 1rem;
        }
        
        .leaf-decoration {
            position: relative;
        }
        
        .leaf-decoration::after {
            content: "❦";
            color: var(--denr-gold);
            font-size: 1.5rem;
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
        }
    </style>
</head>
<body>
    <section class="section section-light" style="min-height: 100vh; display: flex; align-items: center; padding: 20px 0;">
        <div class="px-3 container-fluid">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-6">
                    <!-- Verification Header -->
                    <div class="mb-4 text-center mb-md-5">
                        <div class="mb-3" style="font-size: 3rem;">📧</div>
                        <h1 class="leaf-decoration h2 h1-md">Verify Your Email</h1>
                        <p class="small">Please verify your email address to continue</p>
                    </div>

                    <!-- Verification Card -->
                    <div class="p-3 card p-md-4">
                        <!-- Instructions -->
                        <div class="mb-4 text-center small text-muted" style="line-height: 1.5;">
                            {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
                        </div>

                        <!-- Session Status -->
                        @if (session('status') == 'verification-link-sent')
                            <div class="mb-4 alert alert-success d-flex align-items-center justify-content-center" style="gap: 8px;">
                                <span style="font-size: 1.2rem;">✓</span>
                                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                            </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="gap-3 mt-4 d-flex flex-column flex-sm-row justify-content-between align-items-center">
                            <form method="POST" action="{{ route('verification.send') }}" class="w-100 w-sm-auto">
                                @csrf
                                <button type="submit" class="gap-2 px-4 py-2 btn d-flex align-items-center justify-content-center w-100 w-sm-auto">
                                    <span>↻</span>
                                    {{ __('Resend Verification Email') }}
                                </button>
                            </form>

                            <form method="POST" action="{{ route('logout') }}" class="w-100 w-sm-auto">
                                @csrf
                                <button type="submit" class="gap-2 p-0 btn btn-link text-decoration-underline text-denr-green hover-text-denr-light-green d-flex align-items-center justify-content-center w-100 w-sm-auto">
                                    <span>🚪</span>
                                    {{ __('Log Out') }}
                                </button>
                            </form>
                        </div>

                        <!-- Help Section -->
                        <div class="p-3 mt-4 rounded bg-light">
                            <h4 class="mb-3 text-center text-denr-green h6">Need Help?</h4>
                            <div class="row g-3">
                                <div class="text-center col-12 col-sm-4">
                                    <div class="mb-2" style="font-size: 1.5rem;">📂</div>
                                    <p class="mb-0 small text-muted">Check your spam folder</p>
                                </div>
                                <div class="text-center col-12 col-sm-4">
                                    <div class="mb-2" style="font-size: 1.5rem;">✉️</div>
                                    <p class="mb-0 small text-muted">Verify email address</p>
                                </div>
                                <div class="text-center col-12 col-sm-4">
                                    <div class="mb-2" style="font-size: 1.5rem;">⏰</div>
                                    <p class="mb-0 small text-muted">Wait a few minutes</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        /* Custom styles for the verification page */
        .text-denr-green {
            color: var(--denr-green);
        }
        
        .hover-text-denr-light-green:hover {
            color: var(--denr-light-green);
        }
        
        /* Button hover effects */
        .btn:hover {
            background-color: var(--denr-light-green);
            transform: translateY(-2px);
        }
        
        /* Responsive adjustments */
        @media (max-width: 640px) {
            .card {
                padding: 20px !important;
            }
            
            div[style*="flex"] {
                flex-direction: column;
                gap: 15px;
                align-items: center;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            div[style*="grid-template-columns"] {
                grid-template-columns: 1fr !important;
                gap: 20px !important;
            }
        }
        
        @media (max-width: 480px) {
            .leaf-decoration::after {
                font-size: 1.2rem;
                bottom: -8px;
            }
        }
    </style>
</body>
</html>