<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Verify OTP • EConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
    <style>
        :root {
            --brand: #0b6b32;
            --accent: #46C55E;
            --muted: #6b7280;
            --card-bg: #ffffff;
            --card-shadow: rgba(16,24,40,0.08);
        }

        html,body { height: 100%; }

        body {
            font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(180deg,#eef2f7 0%, #f7fafc 100%);
            color: #0f172a;
            padding: 20px;
            min-height: 100vh;
        }

        /* Centered card like register page — same spacing and structure but for OTP */
        .auth-card {
            width: 100%;
            max-width: 880px;
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 10px 40px var(--card-shadow);
            display: grid;
            grid-template-columns: 1fr 420px;
            overflow: hidden;
        }

        /* left panel matches register page illustration / info */
        .auth-left {
            padding: 44px;
            background: linear-gradient(180deg, rgba(11,107,50,0.06), rgba(11,107,50,0.02));
            display:flex;
            flex-direction:column;
            gap:18px;
            justify-content:center;
        }

        .auth-left .brand {
            display:flex;
            gap:12px;
            align-items:center;
        }
        .brand svg { width:46px; height:46px; }
        .brand h1 { font-size:1.25rem; margin:0; letter-spacing:0.2px; color:var(--brand); }
        .auth-left p { margin:0; color:var(--muted); font-size:0.95rem; }

        .auth-right {
            padding: 42px;
            display:flex;
            flex-direction:column;
            justify-content:center;
            gap:12px;
            min-height: auto;
        }

        .title { font-weight:600; font-size:1.125rem; color:#0f172a; }
        .desc { color:var(--muted); font-size:0.95rem; margin-bottom:8px; }

        /* OTP inputs match register form spacing */
        .otp-row {
            display:flex;
            gap:10px;
            justify-content:center;
            margin:14px 0 4px;
            flex-wrap: wrap;
        }
        .otp-input {
            width:54px;
            height:54px;
            border-radius:8px;
            border:1px solid #e6e9ee;
            background:#fff;
            text-align:center;
            font-size:1.125rem;
            outline:none;
            transition: box-shadow .12s, transform .06s, border-color .08s;
            -webkit-appearance: none;
            appearance: none;
        }
        .otp-input:focus { border-color:var(--accent); box-shadow:0 8px 18px rgba(70,197,94,0.08); transform:translateY(-2px); }
        .otp-input::placeholder { color: #ccc; }

        /* Alerts styling */
        .alert {
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 0.95rem;
            margin-bottom: 8px;
        }
        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .form-actions {
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:12px;
            margin-top:12px;
            flex-wrap: wrap;
        }

        .btn-primary {
            background: linear-gradient(90deg,var(--brand), #145b3a);
            border: none;
            color: #fff;
            padding:10px 18px;
            border-radius:8px;
            cursor: pointer;
            font-weight: 500;
            white-space: nowrap;
            transition: all 0.3s ease;
        }
        .btn-primary:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 12px 24px rgba(11,107,50,0.2); }
        .btn-primary:active:not(:disabled) { transform: translateY(0); }
        .btn-primary:disabled { opacity:.6; cursor: not-allowed; }

        .form-actions > div {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            flex: 1;
        }

        .link-resend { color:var(--brand); text-decoration:none; font-weight:500; cursor: pointer; transition: all 0.2s; border: none; background: none; padding: 4px 8px; }
        .link-resend:hover:not(:disabled) { color: #145b3a; text-decoration: underline; }
        .link-resend:disabled { opacity: 0.6; cursor: not-allowed; color: var(--muted); }
        .muted { color:var(--muted); font-size:0.9rem; }

        .form-footer {
            margin-top:14px;
            font-size:0.86rem;
            color:var(--muted);
            line-height: 1.5;
        }

        /* Error message styling */
        .text-danger {
            color: #dc3545;
            font-size: 0.9rem;
            margin-top: 8px;
        }

        @media (max-width: 980px) {
            .auth-card { grid-template-columns: 1fr; max-width:540px; }
            .auth-left { order:2; padding:20px; text-align:center; }
            .auth-right { padding:28px; }
        }

        @media (max-width:640px) {
            body { padding: 16px; }
            .auth-card { border-radius: 10px; }
            .auth-left { padding: 16px; gap: 14px; }
            .auth-left .brand { justify-content: center; }
            .brand svg { width: 40px; height: 40px; }
            .brand h1 { font-size: 1.1rem; }
            .auth-left p { font-size: 0.9rem; }
            .auth-right { padding: 24px; gap: 10px; }
            .title { font-size: 1rem; }
            .desc { font-size: 0.9rem; margin-bottom: 6px; }
            .otp-input { width:50px; height:50px; font-size:1.05rem; gap: 8px; }
            .form-actions { flex-direction: column; gap: 14px; align-items: stretch; }
            .btn-primary { width: 100%; padding: 12px 18px; font-size: 0.95rem; }
            .form-actions > div { flex-direction: column; }
            .link-resend { display: block; text-align: center; }
            .muted { font-size: 0.85rem; }
            .form-footer { font-size: 0.8rem; }
            .alert { font-size: 0.9rem; padding: 10px 12px; }
        }

        @media (max-width:420px) {
            body { padding: 12px; }
            .auth-card { border-radius: 8px; }
            .auth-left { padding: 14px; gap: 12px; }
            .brand svg { width: 36px; height: 36px; }
            .brand h1 { font-size: 1rem; }
            .auth-left p { font-size: 0.85rem; line-height: 1.4; }
            .auth-right { padding: 20px; gap: 8px; }
            .title { font-size: 0.95rem; font-weight: 600; }
            .desc { font-size: 0.85rem; margin-bottom: 4px; }
            .otp-row { gap: 6px; margin: 12px 0 2px; }
            .otp-input { width: 44px; height: 44px; font-size: 1rem; border-radius: 6px; }
            .form-actions { gap: 10px; }
            .btn-primary { padding: 11px 16px; font-size: 0.9rem; border-radius: 6px; }
            .muted { font-size: 0.8rem; }
            .form-footer { font-size: 0.75rem; margin-top: 10px; }
            .link-resend { font-size: 0.9rem; }
            #timer { font-size: 0.8rem; }
        }

        @media (max-width:375px) {
            body { padding: 10px; }
            .auth-right { padding: 18px; }
            .otp-input { width: 40px; height: 40px; font-size: 0.95rem; }
            .otp-row { gap: 5px; }
            .btn-primary { padding: 10px 14px; font-size: 0.85rem; }
            .title { font-size: 0.9rem; }
            .desc { font-size: 0.8rem; }
        }

        /* Touch device optimizations */
        @media (hover: none) and (pointer: coarse) {
            .otp-input { min-height: 48px; min-width: 48px; }
            .btn-primary { min-height: 48px; padding: 12px 20px; }
            .btn-link { min-height: 44px; padding: 12px 16px; }
        }
    </style>
</head>
<body>
    <div class="auth-card" role="main" aria-labelledby="otp-heading">
        <aside class="auth-left" aria-hidden="true">
            <div class="brand">
                <!-- simple leaf / DENR-like mark -->
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden>
                    <rect width="24" height="24" rx="6" fill="#e9faf0"/>
                    <path d="M7.5 13.5C7.5 10.9 10.1 8.5 12 7.5 13.9 6.5 16.5 7.9 16.5 10.5 16.5 13.1 13.9 15.5 12 16.5 10.1 17.5 7.5 16.1 7.5 13.5z" fill="#0b6b32"/>
                </svg>
                <div>
                    <h1>EConnect</h1>
                    <div class="muted">Secure sign-in for DENR digital services</div>
                </div>
            </div>

            <p>
                For your safety, one-time passcodes (OTPs) are issued for each verification attempt. Keep your code private.
            </p>

            <div class="muted">Need help? Contact DENR support or check your spam folder.</div>
        </aside>

        <section class="auth-right">
            <div>
                <div id="otp-heading" class="title">Verify your email</div>
                <div class="desc">Enter the 6-digit code sent to <strong>{{ $email }}</strong>. This helps protect your account when accessing DENR services.</div>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form id="verifyForm" method="POST" action="{{ route('otp.verify.submit') }}" novalidate>
                @csrf

                <div class="otp-row" aria-label="Enter 6 digit OTP">
                    <input class="otp-input otp-digit" inputmode="numeric" pattern="[0-9]*" maxlength="1" type="text" aria-label="digit-1" />
                    <input class="otp-input otp-digit" inputmode="numeric" pattern="[0-9]*" maxlength="1" type="text" aria-label="digit-2" />
                    <input class="otp-input otp-digit" inputmode="numeric" pattern="[0-9]*" maxlength="1" type="text" aria-label="digit-3" />
                    <input class="otp-input otp-digit" inputmode="numeric" pattern="[0-9]*" maxlength="1" type="text" aria-label="digit-4" />
                    <input class="otp-input otp-digit" inputmode="numeric" pattern="[0-9]*" maxlength="1" type="text" aria-label="digit-5" />
                    <input class="otp-input otp-digit" inputmode="numeric" pattern="[0-9]*" maxlength="1" type="text" aria-label="digit-6" />
                </div>

                <input type="hidden" id="otp_input" name="otp_input">
                @error('otp_input')
                    <div class="mt-2 text-danger small">{{ $message }}</div>
                @enderror

                <div class="form-actions">
                    <button id="submitBtn" class="btn btn-primary" type="submit" disabled aria-label="Verify OTP">Verify</button>

                    <div>
                        <form id="resendForm" method="POST" action="{{ route('otp.resend') }}" class="m-0" style="display: inline;">
                            @csrf
                            <button id="resendBtn" type="submit" class="btn btn-link link-resend" aria-label="Resend OTP">Resend code</button>
                        </form>
                        <div id="timer" class="muted" style="white-space:nowrap; display: inline-block;"></div>
                    </div>
                </div>

                <div class="form-footer">Code expires after a short time. Do not share it with anyone.</div>
            </form>
        </section>
    </div>

    <script>
        // OTP UX and basic resend cooldown logic (unchanged behaviour).
        (function () {
            const digits = Array.from(document.querySelectorAll('.otp-digit'));
            const hidden = document.getElementById('otp_input');
            const submitBtn = document.getElementById('submitBtn');
            const resendBtn = document.getElementById('resendBtn');
            const timerEl = document.getElementById('timer');
            const verifyForm = document.getElementById('verifyForm');
            const resendForm = document.getElementById('resendForm');

            digits.forEach((el, idx) => {
                el.addEventListener('input', (e) => {
                    const v = e.target.value.replace(/\D/g, '');
                    e.target.value = v ? v.slice(0,1) : '';
                    if (v && idx < digits.length - 1) digits[idx+1].focus();
                    update();
                });
                el.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' && !e.target.value && idx > 0) digits[idx-1].focus();
                    if (e.key === 'ArrowLeft' && idx>0) digits[idx-1].focus();
                    if (e.key === 'ArrowRight' && idx<digits.length-1) digits[idx+1].focus();
                });
            });

            function update(){
                const code = digits.map(d=>d.value).join('');
                submitBtn.disabled = (code.length !== digits.length);
            }

            verifyForm.addEventListener('submit', (e) => {
                const code = digits.map(d=>d.value).join('');
                if (code.length !== digits.length) { e.preventDefault(); return; }
                hidden.value = code;
                submitBtn.disabled = true;
            });

            let cooldown = 30, timer;
            function startCooldown(s = 30){
                cooldown = s;
                resendBtn.disabled = true;
                resendBtn.style.pointerEvents = 'none';
                timerEl.textContent = `Resend in ${cooldown}s`;
                timer = setInterval(()=>{
                    cooldown--;
                    if (cooldown <= 0){
                        clearInterval(timer);
                        resendBtn.disabled = false;
                        resendBtn.style.pointerEvents = '';
                        timerEl.textContent = '';
                    } else {
                        timerEl.textContent = `Resend in ${cooldown}s`;
                    }
                }, 1000);
            }

            startCooldown(30);
            resendForm.addEventListener('submit', (e) => {
                if (resendBtn.disabled) { e.preventDefault(); return; }
                resendBtn.disabled = true;
                resendBtn.style.pointerEvents = 'none';
                startCooldown(45);
            });

            if (digits && digits[0]) digits[0].focus();
        })();
    </script>
</body>
</html>
