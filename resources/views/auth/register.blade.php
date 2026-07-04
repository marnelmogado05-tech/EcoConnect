<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Register - EcoConnect</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
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

        /* File Upload Styles */
        .file-upload-container {
            margin-bottom: 24px;
        }

        .file-upload-label {
            display: block;
            font-weight: 500;
            font-size: 0.875rem;
            color: #374151;
            margin-bottom: 8px;
        }

        .file-upload-box {
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
            background-color: #f9fafb;
            cursor: pointer;
        }

        .file-upload-box:hover {
            border-color: var(--denr-green);
            background-color: #f0f9f0;
        }

        .file-upload-box.dragover {
            border-color: var(--denr-green);
            background-color: #e8f5e8;
        }

        .file-upload-icon {
            font-size: 2rem;
            color: var(--denr-green);
            margin-bottom: 10px;
        }

        .file-upload-text {
            margin-bottom: 15px;
        }

        .file-upload-text h4 {
            color: var(--denr-green);
            margin-bottom: 5px;
        }

        .file-upload-text p {
            color: #6b7280;
            font-size: 0.875rem;
        }

        .file-input {
            display: none;
        }

        .file-upload-btn {
            background-color: var(--denr-green);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .file-upload-btn:hover {
            background-color: var(--denr-light-green);
        }

        .file-preview {
            margin-top: 15px;
            display: none;
        }

        .file-preview img {
            max-width: 200px;
            max-height: 150px;
            border-radius: 4px;
            border: 1px solid #d1d5db;
        }

        .file-info {
            margin-top: 10px;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .remove-file {
            color: #ef4444;
            cursor: pointer;
            font-size: 0.875rem;
            margin-left: 10px;
        }

        .remove-file:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <section class="section section-light" style="min-height: 100vh; display: flex; align-items: center; padding: 20px 0;">
        <div class="px-3 container-fluid">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-8 col-xl-6">
                    <!-- Registration Header -->
                    <div class="mb-4 text-center mb-md-5">
                        <a href="{{ route('index') }}"><img src="{{ asset('logo.png') }}" alt="" class="mb-3" style="width: 60px; height: auto;"></a>
                        <h1 class="leaf-decoration h2 h1-md">Create EcoConnect Account</h1>
                        <p class="small">Join our community in protecting the environment</p>
                    </div>

                    <!-- Registration Card -->
                    <div class="p-3 card p-md-4">
                        <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
                            @csrf

                            <!-- Name Fields -->
                            <div class="mb-3 row g-3">
                                <!-- First Name -->
                                <div class="col-12 col-sm-6">
                                    <x-input-label for="fname" :value="__('First Name')" />
                                    <x-text-input
                                        id="fname"
                                        class="mt-1 form-control"
                                        type="text"
                                        name="fname"
                                        :value="old('fname')"
                                        required
                                        autofocus
                                        autocomplete="given-name"
                                        style="padding: 12px; font-size: 16px;"
                                    />
                                    <x-input-error :messages="$errors->get('fname')" class="mt-2" />
                                </div>

                                <!-- Middle Name -->
                                <div class="col-12 col-sm-6">
                                    <x-input-label for="mname" :value="__('Middle Name')" />
                                    <x-text-input
                                        id="mname"
                                        class="mt-1 form-control"
                                        type="text"
                                        name="mname"
                                        :value="old('mname')"
                                        autocomplete="additional-name"
                                        style="padding: 12px; font-size: 16px;"
                                    />
                                    <x-input-error :messages="$errors->get('mname')" class="mt-2" />
                                </div>
                            </div>

                            <div class="mb-3 row g-3">
                                <!-- Last Name -->
                                <div class="col-12 col-sm-6">
                                    <x-input-label for="lname" :value="__('Last Name')" />
                                    <x-text-input
                                        id="lname"
                                        class="mt-1 form-control"
                                        type="text"
                                        name="lname"
                                        :value="old('lname')"
                                        required
                                        autocomplete="family-name"
                                        style="padding: 12px; font-size: 16px;"
                                    />
                                    <x-input-error :messages="$errors->get('lname')" class="mt-2" />
                                </div>

                                <!-- Extension Name -->
                                <div class="col-12 col-sm-6">
                                    <x-input-label for="extname" :value="__('Suffix')" />
                                    <select
                                        id="extname"
                                        name="extname"
                                        class="mt-1 form-control"
                                        style="padding: 12px; font-size: 16px; background-color: white;"
                                    >
                                        <option value="">None</option>
                                        <option value="Jr." {{ old('extname') == 'Jr.' ? 'selected' : '' }}>Jr.</option>
                                        <option value="Sr." {{ old('extname') == 'Sr.' ? 'selected' : '' }}>Sr.</option>
                                        <option value="II" {{ old('extname') == 'II' ? 'selected' : '' }}>II</option>
                                        <option value="III" {{ old('extname') == 'III' ? 'selected' : '' }}>III</option>
                                        <option value="IV" {{ old('extname') == 'IV' ? 'selected' : '' }}>IV</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('extname')" class="mt-2" />
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="mb-3">
                                <x-input-label for="phone" :value="__('Phone Number')" />
                                <x-text-input
                                    id="phone"
                                    class="mt-1 form-control"
                                    type="tel"
                                    name="phone"
                                    :value="old('phone')"
                                    required
                                    autocomplete="tel"
                                    placeholder="09XXXXXXXXX"
                                    style="padding: 12px; font-size: 16px;"
                                />
                                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                            </div>

                            <!-- Location Fields -->
                            {{-- <div class="mb-3 row g-3">
                                <!-- Municipality -->
                                <div class="col-12 col-sm-6">
                                    <x-input-label for="municipality_id" :value="__('Municipality')" />
                                    <select
                                        id="municipality_id"
                                        name="municipality_id"
                                        required
                                        class="mt-1 form-control municipality-select"
                                        style="padding: 12px; font-size: 16px; background-color: white;"
                                    >
                                        <option value="">Select Municipality</option>
                                        @foreach($municipalities as $municipality)
                                            <option value="{{ $municipality->id }}" {{ old('municipality_id') == $municipality->id ? 'selected' : '' }}>
                                                {{ $municipality->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('municipality_id')" class="mt-2" />
                                </div>

                                <!-- Barangay -->
                                <div class="col-12 col-sm-6">
                                    <x-input-label for="barangay_id" :value="__('Barangay')" />
                                    <select
                                        id="barangay_id"
                                        name="barangay_id"
                                        required
                                        class="mt-1 form-control barangay-select"
                                        style="padding: 12px; font-size: 16px; background-color: white;"
                                    >
                                        <option value="">Select Barangay</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('barangay_id')" class="mt-2" />
                                </div>
                            </div> --}}

                            <!-- ID Card Upload -->
                            <div class="mb-3">
                                <x-input-label for="id_card" :value="__('ID Card Upload')" />
                                <div class="p-4 border rounded file-upload-box border-secondary" id="fileUploadBox" style="cursor: pointer; background-color: #f8f9fa;">
                                    <div class="text-center">
                                        <div class="mb-2 file-upload-icon" style="font-size: 2rem;">📷</div>
                                        <div class="file-upload-text">
                                            <h5 class="mb-1">Upload your ID Card</h5>
                                            <p class="mb-3 small text-muted">JPEG, JPG, or PNG (Max: 5MB)</p>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('id_card').click()">
                                            Choose File
                                        </button>
                                    </div>
                                    <input
                                        type="file"
                                        id="id_card"
                                        name="id_card"
                                        class="file-input d-none"
                                        accept=".jpeg,.jpg,.png"
                                        required
                                    >
                                </div>
                                <small class="mt-1 text-muted">Your ID is protected under the <b>Data Privacy Act of 2012 or RA 10173.</b> </small>
                                <div class="mt-3 file-preview d-none" id="filePreview">
                                    <div class="text-center">
                                        <img id="previewImage" src="" alt="ID Card Preview" class="rounded img-fluid" style="max-width: 200px;">
                                        <div class="mt-2">
                                            <small id="fileName" class="text-muted"></small>
                                            <button type="button" class="btn btn-link btn-sm text-danger" onclick="removeFile()">Remove</button>
                                        </div>
                                    </div>
                                </div>
                                <x-input-error :messages="$errors->get('id_card')" class="mt-2" />
                            </div>

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
                                    autocomplete="email"
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
                                    autocomplete="new-password"
                                    style="padding: 12px; font-size: 16px;"
                                />
                                <div id="password-strength" class="mt-2">
                                    <div class="progress" style="height: 5px;">
                                        <div class="progress-bar" id="strength-bar" role="progressbar" style="width: 0%;"></div>
                                    </div>
                                    <small id="strength-text" class="text-muted">Password strength: Weak</small>
                                    <ul id="requirements" class="mt-1 small text-muted">
                                        <li id="req-length">At least 8 characters</li>
                                        <li id="req-uppercase">One uppercase letter</li>
                                        <li id="req-lowercase">One lowercase letter</li>
                                        <li id="req-number">One number</li>
                                        <li id="req-special">One special character</li>
                                    </ul>
                                </div>
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>

                            <!-- Confirm Password -->
                            <div class="mb-3">
                                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                                <x-text-input
                                    id="password_confirmation"
                                    class="mt-1 form-control"
                                    type="password"
                                    name="password_confirmation"
                                    required
                                    autocomplete="new-password"
                                    style="padding: 12px; font-size: 16px;"
                                />
                                <div id="password-match" class="mt-2">
                                    <small id="match-text" class="text-muted">Passwords do not match</small>
                                </div>
                                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                            </div>

                            <!-- Terms and Conditions -->
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="{{ route('terms') }}" target="_blank" class="text-decoration-underline">Terms and Conditions</a>
                                    </label>
                                </div>
                                <x-input-error :messages="$errors->get('terms')" class="mt-2" />
                            </div>

                            <!-- Privacy Policy -->
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="privacy" name="privacy" required>
                                    <label class="form-check-label" for="privacy">
                                        I agree to the <a href="{{ route('privacy') }}" target="_blank" class="text-decoration-underline">Privacy Policy</a>
                                    </label>
                                </div>
                                <x-input-error :messages="$errors->get('privacy')" class="mt-2" />
                            </div>

                            <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>


                            <!-- Submit Button -->
                            <div class="mt-4">

                                @if(session('otp_sent'))
                                <div class="mb-3">
                                    <label for="otp_input" class="form-label">Enter OTP sent to your email</label>
                                    <input type="text" name="otp_input" id="otp_input" class="form-control @error('otp_input') is-invalid @enderror" maxlength="6" required>
                                    @error('otp_input')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3 d-flex justify-content-between align-items-center">
                                    <button type="submit" formaction="{{ route('otp.verify.submit') }}" class="btn btn-primary">Verify OTP</button>
                                    <button type="submit" formaction="{{ route('otp.resend') }}" formmethod="POST" class="btn btn-secondary">Resend OTP</button>
                                    @csrf
                                </div>
                                @else
                                <a class="mb-3 small text-decoration-underline text-denr-green hover-text-denr-light-green mb-sm-0" href="{{ route('login') }}">
                                    {{ __('Already registered?') }}
                                </a>
                                <button type="submit" class="px-4 py-2 btn w-100 w-sm-auto mt-3 mt-sm-0">
                                    {{ __('Register') }}
                                </button>
                                @endif

                            </div>
                    </form>
                    <!-- Add Google reCAPTCHA API -->
                    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
                </div>
            </div>
        </div>
    </section>

    <!-- JavaScript for Dynamic Barangay Loading -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {


        // File upload functionality
        const fileInput = document.getElementById('id_card');
        const fileUploadBox = document.getElementById('fileUploadBox');
        const filePreview = document.getElementById('filePreview');
        const previewImage = document.getElementById('previewImage');
        const fileName = document.getElementById('fileName');

        // File input change event
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file type
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!validTypes.includes(file.type)) {
                    alert('Please upload only JPEG, JPG, or PNG files.');
                    this.value = '';
                    return;
                }

                // Validate file size (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB.');
                    this.value = '';
                    return;
                }

                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    fileName.textContent = file.name;
                    filePreview.style.display = 'block';
                    fileUploadBox.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        });

        // Drag and drop functionality
        fileUploadBox.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });

        fileUploadBox.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });

        fileUploadBox.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                fileInput.dispatchEvent(new Event('change'));
            }
        });
    });

    // Remove file function
    function removeFile() {
        const fileInput = document.getElementById('id_card');
        const filePreview = document.getElementById('filePreview');
        const fileUploadBox = document.getElementById('fileUploadBox');

        fileInput.value = '';
        filePreview.style.display = 'none';
        fileUploadBox.style.display = 'block';
    }
    </script>

    <style>
        /* Custom styles for the registration page */
        .text-denr-green {
            color: var(--denr-green);
        }

        .hover-text-denr-light-green:hover {
            color: var(--denr-light-green);
        }

        /* Input focus states */
        input:focus, select:focus {
            outline: none;
            border-color: var(--denr-green) !important;
            box-shadow: 0 0 0 2px rgba(26, 71, 42, 0.1);
        }

        /* Error message styling */
        .mt-2 {
            margin-top: 8px;
        }

        .text-sm {
            font-size: 0.875rem;
        }

        .text-red-600 {
            color: #dc2626;
        }

        /* Responsive adjustments */
        @media (max-width: 640px) {
            .card {
                padding: 20px !important;
            }

            div[style*="grid-template-columns"] {
                grid-template-columns: 1fr !important;
                gap: 10px !important;
            }
        }
    </style>

    <script>
        const phoneInput = document.getElementById('phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                let digits = e.target.value.replace(/\D/g, '').slice(0, 11);
                let formatted = digits;
                if (digits.length > 4) {
                    formatted = digits.slice(0, 4) + '-' + digits.slice(4, 7);
                    if (digits.length > 7) {
                        formatted += '-' + digits.slice(7, 11);
                    }
                }
                e.target.value = formatted;
            });
        }

        // Password strength checker
        const passwordInput = document.getElementById('password');
        const passwordConfirmInput = document.getElementById('password_confirmation');
        const strengthBar = document.getElementById('strength-bar');
        const strengthText = document.getElementById('strength-text');
        const matchText = document.getElementById('match-text');
        const requirements = {
            length: document.getElementById('req-length'),
            uppercase: document.getElementById('req-uppercase'),
            lowercase: document.getElementById('req-lowercase'),
            number: document.getElementById('req-number'),
            special: document.getElementById('req-special')
        };

        function checkPasswordStrength(password) {
            let score = 0;
            const checks = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /\d/.test(password),
                special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
            };

            // Update requirement list
            Object.keys(checks).forEach(key => {
                const li = requirements[key];
                if (checks[key]) {
                    li.classList.add('text-success');
                    li.innerHTML = li.innerHTML.replace('❌', '✅');
                    if (!li.innerHTML.includes('✅')) {
                        li.innerHTML = '✅ ' + li.innerHTML;
                    }
                } else {
                    li.classList.remove('text-success');
                    li.innerHTML = li.innerHTML.replace('✅', '❌');
                    if (!li.innerHTML.includes('❌')) {
                        li.innerHTML = '❌ ' + li.innerHTML;
                    }
                }
            });

            // Calculate score
            score += checks.length ? 1 : 0;
            score += checks.uppercase ? 1 : 0;
            score += checks.lowercase ? 1 : 0;
            score += checks.number ? 1 : 0;
            score += checks.special ? 1 : 0;

            // Update progress bar and text
            let percentage = (score / 5) * 100;
            strengthBar.style.width = percentage + '%';

            if (score <= 2) {
                strengthBar.className = 'progress-bar bg-danger';
                strengthText.textContent = 'Password strength: Weak';
                strengthText.className = 'text-muted';
            } else if (score <= 4) {
                strengthBar.className = 'progress-bar bg-warning';
                strengthText.textContent = 'Password strength: Medium';
                strengthText.className = 'text-muted';
            } else {
                strengthBar.className = 'progress-bar bg-success';
                strengthText.textContent = 'Password strength: Strong';
                strengthText.className = 'text-success';
            }

            return checks;
        }

        function checkPasswordMatch() {
            const password = passwordInput.value;
            const confirm = passwordConfirmInput.value;

            if (confirm === '') {
                matchText.textContent = '';
                return;
            }

            if (password === confirm) {
                matchText.textContent = '✅ Passwords match';
                matchText.className = 'text-success';
            } else {
                matchText.textContent = '❌ Passwords do not match';
                matchText.className = 'text-danger';
            }
        }

        // Initialize requirements with ❌
        Object.values(requirements).forEach(li => {
            if (!li.innerHTML.includes('❌')) {
                li.innerHTML = '❌ ' + li.innerHTML;
            }
        });

        passwordInput.addEventListener('input', function() {
            checkPasswordStrength(this.value);
            checkPasswordMatch();
        });

        passwordConfirmInput.addEventListener('input', checkPasswordMatch);
    </script>
</body>
</html>
