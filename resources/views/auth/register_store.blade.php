<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register Your Store | OwnStore POS</title>
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
            --success: #10b981;
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
            overflow-x: hidden;
        }

        /* Animated mesh background */
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

        /* Floating grid orbs */
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

        /* Main container */
        .page-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 1050px;
        }

        /* Hero brand section */
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
            letter-spacing: -0.5px;
        }
        .brand-tagline {
            font-size: 0.9rem;
            color: var(--muted);
            font-weight: 400;
        }

        /* Card */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0,0,0,0.5), 0 0 0 1px rgba(99,102,241,0.1);
        }

        .card-grid {
            display: grid;
            grid-template-columns: 1fr 1.45fr;
        }

        /* Left sidebar */
        .sidebar {
            background: linear-gradient(160deg, #0e1329 0%, #111827 100%);
            padding: 2.5rem;
            display: flex;
            flex-direction: column;
            gap: 2rem;
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

        .sidebar-heading {
            position: relative;
        }
        .sidebar-heading h2 {
            font-size: 1.6rem;
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

        .feature-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .feature-list li {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            font-size: 0.875rem;
            color: #94a3b8;
        }
        .feature-list li .fi {
            width: 32px; height: 32px; flex-shrink: 0;
            border-radius: 8px;
            background: rgba(99,102,241,0.12);
            border: 1px solid rgba(99,102,241,0.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 0.8rem;
            color: var(--accent-light);
        }
        .feature-list li div strong {
            display: block;
            color: #cbd5e1;
            font-weight: 600;
            margin-bottom: 2px;
        }

        /* Steps indicator */
        .steps {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            margin-top: auto;
        }
        .step-dot {
            height: 4px;
            border-radius: 2px;
            background: rgba(99,102,241,0.3);
            transition: all 0.4s;
        }
        .step-dot.active { background: var(--accent); width: 28px; }
        .step-dot:not(.active) { width: 8px; }

        /* Form section */
        .form-section {
            padding: 2.5rem;
            background: var(--surface);
        }

        .form-title {
            margin-bottom: 1.75rem;
        }
        .form-title h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text);
        }
        .form-title p {
            font-size: 0.85rem;
            color: var(--muted);
            margin-top: 0.3rem;
        }

        /* Alert */
        .alert {
            background: rgba(244,63,94,0.1);
            border: 1px solid rgba(244,63,94,0.3);
            border-radius: 12px;
            padding: 0.85rem 1rem;
            margin-bottom: 1.25rem;
            font-size: 0.83rem;
            color: #fda4af;
        }
        .alert ul { list-style: none; display: flex; flex-direction: column; gap: 0.25rem; }
        .alert ul li::before { content: '⚠ '; }

        /* Section label */
        .section-label {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--accent-light);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* Field group */
        .field-group { margin-bottom: 1.1rem; }
        .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }

        label {
            display: block;
            font-size: 0.78rem;
            font-weight: 600;
            color: #94a3b8;
            margin-bottom: 0.4rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .input-wrap {
            position: relative;
        }
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

        input, select {
            width: 100%;
            background: var(--surface2);
            border: 1.5px solid rgba(255,255,255,0.07);
            border-radius: 10px;
            padding: 0.65rem 0.9rem 0.65rem 2.4rem;
            font-family: 'Outfit', sans-serif;
            font-size: 0.9rem;
            color: var(--text);
            outline: none;
            transition: border-color 0.25s, box-shadow 0.25s;
            appearance: none;
        }
        input::placeholder { color: rgba(100,116,139,0.7); font-weight: 400; }
        input:focus, select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
        }
        input:focus + .icon, select:focus + .icon {
            color: var(--accent-light);
        }
        .input-wrap:has(input:focus) .icon,
        .input-wrap:has(select:focus) .icon {
            color: var(--accent-light);
        }

        /* Select arrow */
        select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='%2364748b' d='M4 6l4 4 4-4'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.8rem center;
            background-size: 16px;
            padding-right: 2.2rem;
        }
        select option { background: #161c2e; }

        /* Password toggle */
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

        /* Divider */
        .divider {
            height: 1px;
            background: var(--border);
            margin: 1.4rem 0;
        }

        /* Submit button */
        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 0.9rem;
            font-family: 'Outfit', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: 0.02em;
            box-shadow: 0 8px 20px rgba(99,102,241,0.35);
            transition: transform 0.2s, box-shadow 0.2s, opacity 0.2s;
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

        /* Login link */
        .login-link {
            text-align: center;
            font-size: 0.83rem;
            color: var(--muted);
            margin-top: 1.25rem;
        }
        .login-link a {
            color: var(--accent-light);
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
        }
        .login-link a:hover { color: white; }

        /* Trust badges */
        .trust-row {
            display: flex;
            justify-content: center;
            gap: 1.25rem;
            margin-top: 1.25rem;
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
            .form-section { padding: 1.75rem; }
            .field-row { grid-template-columns: 1fr; }
        }
    </style>
</head>

<body>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="page-wrapper">

        <!-- Brand header -->
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
                        <h2>Start managing<br>your business today</h2>
                        <p>Register your store and get access to a complete POS, inventory, and accounting platform.</p>
                    </div>

                    <ul class="feature-list">
                        <li>
                            <div class="fi"><i class="fas fa-bolt"></i></div>
                            <div>
                                <strong>Fast POS Billing</strong>
                                Scan barcodes, process sales in seconds
                            </div>
                        </li>
                        <li>
                            <div class="fi"><i class="fas fa-boxes-stacked"></i></div>
                            <div>
                                <strong>FIFO Inventory &amp; Stock Alerts</strong>
                                Real-time stock tracking with low-stock alerts
                            </div>
                        </li>
                        <li>
                            <div class="fi"><i class="fas fa-file-import"></i></div>
                            <div>
                                <strong>Excel Bulk Import</strong>
                                Migrate products from your old POS instantly
                            </div>
                        </li>
                        <li>
                            <div class="fi"><i class="fas fa-chart-line"></i></div>
                            <div>
                                <strong>Sales &amp; Financial Reports</strong>
                                Profit &amp; loss, ledger, and daily summaries
                            </div>
                        </li>
                        <li>
                            <div class="fi"><i class="fas fa-users"></i></div>
                            <div>
                                <strong>Multi-User with Role Control</strong>
                                Owner, manager, and cashier permissions
                            </div>
                        </li>
                    </ul>

                    <div>
                        <div class="steps">
                            <div class="step-dot active"></div>
                            <div class="step-dot"></div>
                            <div class="step-dot"></div>
                        </div>
                        <p style="font-size:0.72rem;color:var(--muted);margin-top:0.5rem;">Step 1 of 1 — Fill in your store details</p>
                    </div>
                </div>

                <!-- RIGHT FORM -->
                <div class="form-section">
                    <div class="form-title">
                        <h3>Create Store Account</h3>
                        <p>Takes less than 2 minutes. No credit card required.</p>
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

                    <form method="POST" action="{{ route('store.register') }}">
                        @csrf

                        <!-- Owner Info -->
                        <div class="section-label"><i class="fas fa-user-tie"></i> Owner Information</div>

                        <div class="field-row">
                            <div class="field-group">
                                <label for="owner_name">Full Name</label>
                                <div class="input-wrap">
                                    <i class="fas fa-user icon"></i>
                                    <input id="owner_name" type="text" name="owner_name" value="{{ old('owner_name') }}"
                                           placeholder="Muhammad Yaseen" required autocomplete="off">
                                </div>
                            </div>
                            <div class="field-group">
                                <label for="phone">Phone Number</label>
                                <div class="input-wrap">
                                    <i class="fas fa-phone icon"></i>
                                    <input id="phone" type="text" name="phone" value="{{ old('phone') }}"
                                           placeholder="+92 3xx xxxxxxx" required>
                                </div>
                            </div>
                        </div>

                        <div class="field-group">
                            <label for="email">Email Address</label>
                            <div class="input-wrap">
                                <i class="fas fa-envelope icon"></i>
                                <input id="email" type="email" name="email" value="{{ old('email') }}"
                                       placeholder="owner@example.com" required autocomplete="off">
                            </div>
                        </div>

                        <div class="divider"></div>

                        <!-- Store Info -->
                        <div class="section-label"><i class="fas fa-store"></i> Store Details</div>

                        <div class="field-row">
                            <div class="field-group">
                                <label for="store_name">Store Name</label>
                                <div class="input-wrap">
                                    <i class="fas fa-tag icon"></i>
                                    <input id="store_name" type="text" name="store_name" value="{{ old('store_name') }}"
                                           placeholder="My Supermarket" required>
                                </div>
                            </div>
                            <div class="field-group">
                                <label for="business_type">Business Type</label>
                                <div class="input-wrap">
                                    <i class="fas fa-briefcase icon"></i>
                                    <select id="business_type" name="business_type" required>
                                        <option value="" disabled {{ old('business_type') ? '' : 'selected' }}>Select type...</option>
                                        <option value="Super Market"   {{ old('business_type') == 'Super Market'   ? 'selected' : '' }}>🛒 Super Market</option>
                                        <option value="Medical Store"  {{ old('business_type') == 'Medical Store'  ? 'selected' : '' }}>💊 Medical Store</option>
                                        <option value="Shoe Shop"      {{ old('business_type') == 'Shoe Shop'      ? 'selected' : '' }}>👟 Shoe Shop</option>
                                        <option value="Retail Store"   {{ old('business_type') == 'Retail Store'   ? 'selected' : '' }}>🏪 Retail Store</option>
                                        <option value="Restaurant"     {{ old('business_type') == 'Restaurant'     ? 'selected' : '' }}>🍽️ Restaurant</option>
                                        <option value="Bakery"         {{ old('business_type') == 'Bakery'         ? 'selected' : '' }}>🍞 Bakery</option>
                                        <option value="Electronics"    {{ old('business_type') == 'Electronics'    ? 'selected' : '' }}>📱 Electronics</option>
                                        <option value="Clothing"       {{ old('business_type') == 'Clothing'       ? 'selected' : '' }}>👗 Clothing</option>
                                        <option value="Other"          {{ old('business_type') == 'Other'          ? 'selected' : '' }}>📦 Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="divider"></div>

                        <!-- Password -->
                        <div class="section-label"><i class="fas fa-lock"></i> Security</div>

                        <div class="field-row">
                            <div class="field-group">
                                <label for="password">Password</label>
                                <div class="input-wrap">
                                    <i class="fas fa-key icon"></i>
                                    <input id="password" type="password" name="password" placeholder="Min 8 characters" required>
                                    <button type="button" class="pass-toggle" onclick="togglePass('password', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="field-group">
                                <label for="password_confirmation">Confirm Password</label>
                                <div class="input-wrap">
                                    <i class="fas fa-key icon"></i>
                                    <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Repeat password" required>
                                    <button type="button" class="pass-toggle" onclick="togglePass('password_confirmation', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div style="margin-top:1.75rem;">
                            <button type="submit" class="btn-submit" id="submitBtn">
                                <i class="fas fa-rocket" style="margin-right:0.5rem;"></i> Create My Store
                            </button>
                        </div>

                        <p class="login-link">
                            Already have an account?
                            <a href="{{ route('login') }}">Login to Dashboard →</a>
                        </p>
                    </form>

                    <div class="trust-row">
                        <span class="trust-badge"><i class="fas fa-shield-halved"></i> Secure &amp; Encrypted</span>
                        <span class="trust-badge"><i class="fas fa-check-circle"></i> No Credit Card</span>
                        <span class="trust-badge"><i class="fas fa-server"></i> Runs Locally</span>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <script>
        function togglePass(id, btn) {
            const input = document.getElementById(id);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Button loading state on submit
        document.querySelector('form').addEventListener('submit', function () {
            const btn = document.getElementById('submitBtn');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right:0.5rem;"></i> Creating Store...';
            btn.style.opacity = '0.8';
            btn.disabled = true;
        });
    </script>

</body>
</html>