<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login - EcoConnect</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
                <div class="col-12 col-md-6 col-lg-4">
                    <!-- Login Header -->
                    <div class="mb-4 text-center mb-md-5">
                        <a href="{{ route('index') }}"><img src="{{ asset('logo.png') }}" alt="" class="mb-3" style="width: 60px; height: auto;"></a>

                        <h1 class="leaf-decoration h2 h1-md">Login to EcoConnect</h1>
                        <p class="small">Access your DENR Environmental Incident Reporting account</p>
                    </div>

                    <!-- Session Status -->
                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    <!-- Login Card -->
                    <div class="p-3 card p-md-4 p-lg-5">
                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <!-- Email Address -->
                            <div class="mb-3">
                                <x-input-label for="email" :value="__('Email')" />
                                <x-text-input
                                    id="email"
                                    class="mt-1 form-control"
                                    type="email"
                                    name="email"
                                    :value="old('email')"
                                    required
                                    autofocus
                                    autocomplete="username"
                                    style="padding: 12px; font-size: 16px;"
                                />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <!-- Password -->
                            <div class="mb-3">
                                <x-input-label for="password" :value="__('Password')" />
                                <x-text-input
                                    id="password"
                                    class="mt-1 form-control"
                                    type="password"
                                    name="password"
                                    required
                                    autocomplete="current-password"
                                    style="padding: 12px; font-size: 16px;"
                                />
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>

                            <!-- Remember Me & Forgot Password -->
                            <div class="mb-3 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center">
                                <label for="remember_me" class="mb-2 d-flex align-items-center mb-sm-0" style="cursor: pointer;">
                                    <input
                                        id="remember_me"
                                        type="checkbox"
                                        class="me-2"
                                        name="remember"
                                        style="width: 16px; height: 16px;"
                                    >
                                    <span class="small text-muted">{{ __('Remember me') }}</span>
                                </label>

                                @if (Route::has('password.request'))
                                    <a class="small text-decoration-underline text-denr-green hover-text-denr-light-green" href="{{ route('password.request') }}">
                                        {{ __('Forgot your password?') }}
                                    </a>
                                @endif
                            </div>

                            <!-- Login Button -->
                            <button type="submit" class="py-3 btn w-100" style="font-size: 16px;">
                                {{ __('Log in') }}
                            </button>
                        </form>

                        <!-- Additional Options -->
                        <div class="pt-3 mt-4 text-center border-top">
                            <p class="mb-3 small text-muted">Don't have an account?</p>
                            <a href="{{ route('register') }}" class="btn btn-outline d-block d-sm-inline-block">
                                {{ __('Create Account') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        /* Custom styles for the login page */
        .text-denr-green {
            color: var(--denr-green);
        }

        .hover-text-denr-light-green:hover {
            color: var(--denr-light-green);
        }

        /* Input focus states */
        input:focus {
            outline: none;
            border-color: var(--denr-green) !important;
            box-shadow: 0 0 0 2px rgba(26, 71, 42, 0.1);
        }

        /* Custom styling for error messages */
        .mt-2 {
            margin-top: 8px;
        }

        .text-sm {
            font-size: 0.875rem;
        }

        .text-red-600 {
            color: #dc2626;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        @if(session('error'))
            setTimeout(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: '{{ session('error') }}',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#d33'
                });
            }, 500);
        @endif

        @if(session('success'))
            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '{{ session('success') }}',
                    timer: 3000,
                    showConfirmButton: false
                });
            }, 500);
        @endif
    </script>
</body>
</html>
