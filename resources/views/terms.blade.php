@extends('layouts.guest')
@section('title', 'Terms and Conditions')
@section('content')
    <div class="container px-4 py-8 mx-auto">
        <div class="max-w-4xl mx-auto">
            <h1 class="mb-8 text-3xl font-bold text-gray-900">Terms and Conditions</h1>

            <div class="text-gray-700">
                <h2 class="mb-4 text-xl font-semibold">1. Acceptance of Terms</h2>
                <p class="mb-4">By accessing and using EcoConnect, you accept and agree to be bound by the terms and provision of this agreement.</p>

                <h2 class="mb-4 text-xl font-semibold">2. Use License</h2>
                <p class="mb-4">Permission is granted to temporarily download one copy of EcoConnect for personal, non-commercial transitory viewing only.</p>

                <h2 class="mb-4 text-xl font-semibold">3. User Responsibilities</h2>
                <p class="mb-4">Users are responsible for maintaining the confidentiality of their account and password and for restricting access to their computer.</p>

                <h2 class="mb-4 text-xl font-semibold">4. Privacy</h2>
                <p class="mb-4">Your privacy is important to us. Please review our Privacy Policy, which also governs your use of EcoConnect.</p>

                <h2 class="mb-4 text-xl font-semibold">5. Content</h2>
                <p class="mb-4">Our Service allows you to post, link, store, share and otherwise make available certain information, text, graphics, or other material.</p>

                <h2 class="mb-4 text-xl font-semibold">6. Termination</h2>
                <p class="mb-4">We may terminate or suspend your account immediately, without prior notice or liability, for any reason whatsoever.</p>

                <h2 class="mb-4 text-xl font-semibold">7. Changes</h2>
                <p class="mb-4">We reserve the right, at our sole discretion, to modify or replace these Terms at any time.</p>

                <p class="mt-8 text-sm text-gray-600">Last updated: {{ date('F j, Y') }}</p>
            </div>
        </div>
    </div>
@endsection
