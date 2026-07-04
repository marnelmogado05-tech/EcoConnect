@extends('layouts.guest')
@section('title', 'Terms and Conditions')
@section('content')
    <div class="container px-4 py-8 mx-auto">
        <div class="max-w-4xl mx-auto">
            <h1 class="mb-8 text-3xl font-bold text-gray-900">Privacy Policy</h1>

            <div class="text-gray-700">
                <h2 class="mb-4 text-xl font-semibold">1. Information We Collect</h2>
                <p class="mb-4">We collect information you provide directly to us, such as when you create an account, submit incident reports, or contact us for support.</p>

                <h2 class="mb-4 text-xl font-semibold">2. How We Use Your Information</h2>
                <p class="mb-4">We use the information we collect to provide, maintain, and improve our services, process transactions, and communicate with you.</p>

                <h2 class="mb-4 text-xl font-semibold">3. Information Sharing</h2>
                <p class="mb-4">We do not sell, trade, or otherwise transfer your personal information to third parties without your consent, except as described in this policy.</p>

                <h2 class="mb-4 text-xl font-semibold">4. Data Security</h2>
                <p class="mb-4">We implement appropriate security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction.</p>

                <h2 class="mb-4 text-xl font-semibold">5. Data Retention</h2>
                <p class="mb-4">We retain personal information for as long as necessary to provide our services and fulfill the purposes outlined in this privacy policy.</p>

                <h2 class="mb-4 text-xl font-semibold">6. Your Rights</h2>
                <p class="mb-4">You have the right to access, update, or delete your personal information. You may also object to or restrict certain processing of your information.</p>

                <h2 class="mb-4 text-xl font-semibold">7. Cookies</h2>
                <p class="mb-4">We use cookies and similar technologies to enhance your experience on our platform.</p>

                <h2 class="mb-4 text-xl font-semibold">8. Changes to This Policy</h2>
                <p class="mb-4">We may update this privacy policy from time to time. We will notify you of any changes by posting the new policy on this page.</p>

                <h2 class="mb-4 text-xl font-semibold">9. Contact Us</h2>
                <p class="mb-4">If you have any questions about this Privacy Policy, please contact us.</p>

                <p class="mt-8 text-sm text-gray-600">Last updated: {{ date('F j, Y') }}</p>
            </div>
        </div>
    </div>
@endsection
