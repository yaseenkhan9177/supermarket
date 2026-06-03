<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login | OwnStore POS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg: #080b14;
            --surface: #0f1420;
            --surface2: #161c2e;
            --border: rgba(99,102,241,0.2);
            --text: #e2e8f0;
            --muted: #64748b;
            --accent: #6366f1;
            --accent-glow: rgba(99,102,241,0.35);
            --accent-light: #818cf8;
            --danger: #f43f5e;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            color: var(--text);
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 80% at 20% -10%, rgba(99,102,241,0.15) 0%, transparent 60%),
                radial-gradient(ellipse 60% 60% at 80% 110%, rgba(139,92,246,0.12) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.18;
            pointer-events: none;
            z-index: 0;
        }
        .orb-1 { width: 400px; height: 400px; background: #6366f1; top: -100px; left: -100px; animation: float 12s ease-in-out infinite; }
        .orb-2 { width: 300px; height: 300px; background: #8b5cf6; bottom: -80px; right: -80px; animation: float 15s ease-in-out infinite reverse; }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(30px, 20px) scale(1.05); }
        }

        .page-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 950px;
        }

        .brand-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        .brand-logo {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 0.8rem;
        }
        .brand-logo .logo-icon {
            width: 42px; height: 42px;
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem; color: white;
            box-shadow: 0 0 20px var(--accent-glow);
        }
        .brand-logo span {
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #a5b4fc, white);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .brand-tagline {
            font-size: 0.9rem;
            color: var(--muted);
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0,0,0,0.5), 0 0 0 1px rgba(99,102,241,0.1);
        }

        .card-grid {
            display: grid;
            grid-template-columns: 1fr 1.3fr;
        }

        /* Sidebar */
        .sidebar {
            background: linear-gradient(160deg, #0e1329 0%, #111827 100%);
            padding: 3rem 2.5rem;
            display: flex;
            flex-direction: column;
            gap: 2.5rem;
            border-right: 1px solid var(--border);
            position: relative;
            overflow: hidden;
        }
        .sidebar::before {
            content: '';
            position: absolute;
            top: -60px; left: -60px;
            width: 250px; height: 250px;
            background: radial-gradient(circle, rgba(99,102,241,0.15) 0%, transparent 70%);
            pointer-events: none;
        }
        .sidebar-heading h2 {
            font-size: 1.7rem;
            font-weight: 800;
            background: linear-gradient(135deg, #e2e8f0, #a5b4fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1.3;
        }
        .sidebar-heading p {
            margin-top: 0.6rem;
            font-size: 0.85rem;
            color: var(--muted);
            line-height: 1.6;
        }

        /* User type selector */
        .user-types {
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
        }
        .user-type-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.8rem 1rem;
            border-radius: 12px;
            border: 1px solid rgba(99,102,241,0.1);
            background: rgba(99,102,241,0.05);
            cursor: default;
            transition: border-color 0.2s, background 0.2s;
        }
        .user-type-item:first-child {
            border-color: rgba(99,102,241,0.4);
            background: rgba(99,102,241,0.1);
        }
        .user-type-icon {
            width: 34px; height: 34px; flex-shrink: 0;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.8rem;
        }
        .user-type-icon.owner { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; }
        .user-type-icon.emp { background: rgba(16,185,129,0.15); color: #34d399; border: 1px solid rgba(16,185,129,0.3); }
        .user-type-icon.admin { background: rgba(245,158,11,0.1); color: #fbbf24; border: 1px solid rgba(245,158,11,0.25); }
        .user-type-item div strong {
            display: block;
            font-size: 0.83rem;
            font-weight: 600;
            color: #cbd5e1;
        }
        .user-type-item div span {
            font-size: 0.72rem;
            color: var(--muted);
        }

        /* Form section */
        .form-section {
            padding: 3rem 2.5rem;
            background: var(--surface);
        }

        .form-title {
            margin-bottom: 2rem;
        }
        .form-title h3 {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--text);
        }
        .form-title p {
            font-size: 0.85rem;
            color: var(--muted);
            margin-top: 0.3rem;
        }

        .alert {
            background: rgba(244,63,94,0.1);
            border: 1px solid rgba(244,63,94,0.3);
            border-radius: 12px;
            padding: 0.85rem 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.83rem;
            color: #fda4af;
        }
        .alert ul { list-style: none; }
        .alert ul li::before { content: '⚠ '; }

        .field-group { margin-bottom: 1.2rem; }

        label {
            display: block;
            font-size: 0.78rem;
            font-weight: 600;
            color: #94a3b8;
            margin-bottom: 0.4rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .input-wrap { position: relative; }
        .input-wrap .icon {
            position: absolute;
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            font-size: 0.85rem;
            pointer-events: none;
            transition: color 0.2s;
        }

        input {
            width: 100%;
            background: var(--surface2);
            border: 1.5px solid rgba(255,255,255,0.07);
            border-radius: 12px;
            padding: 0.85rem 0.9rem 0.85rem 2.5rem;
            font-family: 'Outfit', sans-serif;
            font-size: 0.95rem;
            color: var(--text);
            outline: none;
            transition: border-color 0.25s, box-shadow 0.25s;
        }
        input::placeholder { color: rgba(100,116,139,0.7); }
        input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
        }
        .input-wrap:has(input:focus) .icon { color: var(--accent-light); }

        .pass-toggle {
            position: absolute;
            right: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--muted);
            font-size: 0.85rem;
            transition: color 0.2s;
            padding: 0;
        }
        .pass-toggle:hover { color: var(--text); }

        .form-extras {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.75rem;
            margin-top: -0.25rem;
        }
        .remember-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            font-size: 0.8rem;
            color: var(--muted);
        }
        .remember-label input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--accent);
            padding: 0;
            border-radius: 4px;
        }
        .forgot-link {
            font-size: 0.8rem;
            color: var(--accent-light);
            text-decoration: none;
            font-weight: 500;
        }
        .forgot-link:hover { color: white; }

        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 1rem;
            font-family: 'Outfit', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: 0.02em;
            box-shadow: 0 8px 20px rgba(99,102,241,0.35);
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
            overflow: hidden;
        }
        .btn-submit::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.15) 0%, transparent 60%);
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(99,102,241,0.45);
        }
        .btn-submit:active { transform: translateY(0); }

        .register-link {
            text-align: center;
            font-size: 0.83rem;
            color: var(--muted);
            margin-top: 1.25rem;
        }
        .register-link a {
            color: var(--accent-light);
            font-weight: 600;
            text-decoration: none;
        }
        .register-link a:hover { color: white; }

        .divider-or {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 1.5rem 0;
            color: var(--muted);
            font-size: 0.75rem;
        }
        .divider-or::before, .divider-or::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .trust-row {
            display: flex;
            justify-content: center;
            gap: 1.25rem;
            margin-top: 1.5rem;
        }
        .trust-badge {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.72rem;
            color: var(--muted);
        }
        .trust-badge i { color: var(--accent-light); }

        @media (max-width: 768px) {
            .card-grid { grid-template-columns: 1fr; }
            .sidebar { display: none; }
            .form-section { padding: 2rem 1.5rem; }
        }
    </style>
</head>

<body>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="page-wrapper">

        <div class="brand-header">
            <div class="brand-logo">
                <div class="logo-icon"><i class="fas fa-store"></i></div>
                <span>OwnStore</span>
            </div>
            <p class="brand-tagline">Smart POS &amp; Inventory Management System</p>
        </div>

        <div class="card">
            <div class="card-grid">

                <!-- LEFT SIDEBAR -->
                <div class="sidebar">
                    <div class="sidebar-heading">
                        <h2>Welcome back to your store</h2>
                        <p>Log in to access your POS dashboard, inventory, reports, and more.</p>
                    </div>

                    <div>
                        <p style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--muted);margin-bottom:0.75rem;">Login as</p>
                        <div class="user-types">
                            <div class="user-type-item">
                                <div class="user-type-icon owner"><i class="fas fa-crown"></i></div>
                                <div>
                                    <strong>Store Owner</strong>
                                    <span>Full access to all features</span>
                                </div>
                            </div>
                            <div class="user-type-item">
                                <div class="user-type-icon emp"><i class="fas fa-user-tie"></i></div>
                                <div>
                                    <strong>Employee / Cashier</strong>
                                    <span>Access based on assigned role</span>
                                </div>
                            </div>
                            <div class="user-type-item">
                                <div class="user-type-icon admin"><i class="fas fa-shield-halved"></i></div>
                                <div>
                                    <strong>Super Admin</strong>
                                    <span>System-wide management</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top:auto;">
                        <p style="font-size:0.75rem;color:var(--muted);line-height:1.6;">
                            One login form works for all account types — the system detects your role automatically.
                        </p>
                    </div>
                </div>

                <!-- RIGHT FORM -->
                <div class="form-section">
                    <div class="form-title">
                        <h3>Login to Account</h3>
                        <p>Enter your credentials to access the dashboard.</p>
                    </div>

                    @if ($errors->any())
                    <div class="alert">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="field-group">
                            <label for="email">Email Address</label>
                            <div class="input-wrap">
                                <i class="fas fa-envelope icon"></i>
                                <input id="email" type="email" name="email"
                                       value="{{ old('email') }}"
                                       placeholder="name@company.com"
                                       required autocomplete="email">
                            </div>
                        </div>

                        <div class="field-group">
                            <label for="password">Password</label>
                            <div class="input-wrap">
                                <i class="fas fa-lock icon"></i>
                                <input id="password" type="password" name="password"
                                       placeholder="••••••••"
                                       required autocomplete="current-password">
                                <button type="button" class="pass-toggle" onclick="togglePass()">
                                    <i class="fas fa-eye" id="passEyeIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-extras">
                            <label class="remember-label">
                                <input type="checkbox" name="remember" id="remember">
                                Keep me logged in
                            </label>
                            <a href="#" class="forgot-link">Forgot password?</a>
                        </div>

                        <button type="submit" class="btn-submit" id="loginBtn">
                            <i class="fas fa-arrow-right-to-bracket" style="margin-right:0.5rem;"></i>
                            Login to Dashboard
                        </button>

                        <div class="divider-or">or</div>

                        <p class="register-link">
                            Don't have a store account?
                            <a href="{{ route('store.register.form') }}">Register Now →</a>
                        </p>

                    </form>

                    <div class="trust-row">
                        <span class="trust-badge"><i class="fas fa-shield-halved"></i> Secure Login</span>
                        <span class="trust-badge"><i class="fas fa-server"></i> Runs Locally</span>
                        <span class="trust-badge"><i class="fas fa-users"></i> Multi-Role</span>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <script>
        function togglePass() {
            const input = document.getElementById('password');
            const icon = document.getElementById('passEyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        document.querySelector('form').addEventListener('submit', function () {
            const btn = document.getElementById('loginBtn');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right:0.5rem;"></i> Logging in...';
            btn.style.opacity = '0.8';
            btn.disabled = true;
        });
    </script>

</body>
</html>