
@extends($layout)

@section('title', 'Profile - EcoConnect')
@section('page-title', 'Profile')
@section('content')
    <div class="profile-container">
        <!-- Update Profile Information -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Profile Information</h2>
                <p class="text-sm text-gray-600">Update your account's profile information and email address.</p>
            </div>
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <!-- Update Password -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Update Password</h2>
                <p class="text-sm text-gray-600">Ensure your account is using a long, random password to stay secure.</p>
            </div>
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <!-- Delete Account -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Delete Account</h2>
                <p class="text-sm text-gray-600">Permanently delete your account.</p>
            </div>
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>

    <style>
        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .max-w-xl {
            max-width: 100%;
        }

        /* Form Styling */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--denr-green);
        }

        .form-input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--denr-green);
            box-shadow: 0 0 0 3px rgba(26, 71, 42, 0.1);
        }

        .form-input.error {
            border-color: #ef4444;
        }

        .error-message {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 5px;
        }

        .success-message {
            color: #22c55e;
            font-size: 0.875rem;
            margin-top: 5px;
        }

        /* Button Styles */
        .btn-primary {
            background: var(--denr-green);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--denr-light-green);
            transform: translateY(-1px);
        }

        .btn-danger {
            background: #ef4444;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-danger:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .form-input {
                padding: 10px;
            }

            .btn-primary, .btn-danger {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endsection