@extends('layouts.guest')

@section('title', 'EcoConnect - Home')

<style>
    @media (max-width: 768px) {
        .hero {
            padding: 100px 0 80px;
        }
        .hero h1 {
            font-size: 2rem;
        }
        .hero p {
            font-size: 1rem;
        }
        .container {
            padding: 0 15px;
        }
        .section {
            padding: 50px 0;
        }
        .hero-buttons {
            flex-direction: column !important;
            gap: 15px !important;
        }
        .hero-buttons .btn {
            margin-right: 0 !important;
        }
    }
</style>

@section('content')
    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="container text-center">
            <h1>Protect Our Environment Together</h1>
            <p>EcoConnect empowers citizens to report environmental incidents directly to DENR. Help us safeguard our natural resources for future generations.</p>
            <div class="gap-3 hero-buttons d-flex flex-md-row flex-column justify-content-center align-items-center" style="margin-top: 30px;">
                <a href="{{ route('register') }}" class="btn">Report an Incident</a>
                <a href="{{ route('track.index') }}" class="btn btn-outline">Track a Report</a>
            </div>
        </div>
    </section>

    <!-- Centered Map -->
    <div class="my-5 d-flex justify-content-center align-items-center">
        <div class="ratio ratio-16x9" style="max-width: 600px; width: 100%; border-radius: 10px; overflow: hidden;">
            <iframe
                src="https://www.google.com/maps/embed?pb=!4v1759809311948!6m8!1m7!1sEZ0GnyTSNh5r69NrMO_pag!2m2!1d18.56690372584928!2d121.2254911077001!3f38.175551772187134!4f5.405605828256597!5f1.5356406747036249"
                style="border:0;"
                allowfullscreen
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </div>

    <!-- Features Section -->
    <section class="section section-light">
        <div class="container">
            <h2 class="text-center leaf-decoration">How EcoConnect Works</h2>
            <p class="text-center" style="max-width: 800px; margin: 0 auto 50px;">Our simple three-step process makes environmental reporting accessible to everyone.</p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
                <div class="text-center card">
                    <div class="feature-icon">📝</div>
                    <h3>1. Report Incident</h3>
                    <p>Submit detailed information about environmental concerns like pollution, illegal logging, or wildlife issues.</p>
                </div>
                
                <div class="text-center card">
                    <div class="feature-icon">🔍</div>
                    <h3>2. DENR Review</h3>
                    <p>Our DENR specialists assess your report and determine the appropriate response action.</p>
                </div>
                
                <div class="text-center card">
                    <div class="feature-icon">✅</div>
                    <h3>3. Track Progress</h3>
                    <p>Monitor the status of your report and see how DENR is addressing the environmental concern.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="section section-light" id="about">
        <div class="container">
            <h2 class="text-center leaf-decoration">About EcoConnect</h2>
            <div style="max-width: 1000px; margin: 0 auto;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px; align-items: start;">
                    <div>
                        <h3>Our Mission</h3>
                        <p>To empower citizens and communities to actively participate in environmental protection by providing a seamless platform for reporting incidents and collaborating with DENR to ensure timely and effective responses.</p>
                    </div>
                    <div>
                        <h3>Our Vision</h3>
                        <p>A Philippines where every citizen is engaged in safeguarding our natural resources, leading to a sustainable and thriving environment for current and future generations.</p>
                    </div>
                    <div>
                        <h3>Why EcoConnect?</h3>
                        <p>EcoConnect bridges the gap between concerned citizens and environmental authorities. Our platform ensures that environmental concerns are heard, documented, and addressed promptly, fostering transparency and accountability in environmental governance.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="section section-green">
        <div class="container text-center">
            <h2>Ready to Make a Difference?</h2>
            <p style="max-width: 700px; margin: 0 auto 30px;">Your report could help protect endangered species, prevent pollution, or preserve natural habitats. Every action counts.</p>
            <a href="{{ route('register') }}" class="btn" style="background-color: var(--denr-gold); color: var(--denr-green);">Report an Environmental Incident Now</a>
        </div>
    </section>
@endsection
