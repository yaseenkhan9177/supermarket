<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin — OwnStore</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }

        body {
            background: radial-gradient(ellipse at 20% 50%, rgba(99,102,241,0.15) 0%, transparent 50%),
                        radial-gradient(ellipse at 80% 20%, rgba(139,92,246,0.15) 0%, transparent 50%),
                        radial-gradient(ellipse at 50% 80%, rgba(59,130,246,0.1) 0%, transparent 50%),
                        #0f172a;
            min-height: 100vh;
        }

        /* Floating orbs */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.15;
            animation: drift 12s ease-in-out infinite alternate;
        }
        .orb-1 { width: 400px; height: 400px; background: #6366f1; top: -100px; left: -100px; }
        .orb-2 { width: 300px; height: 300px; background: #8b5cf6; bottom: -80px; right: -80px; animation-delay: -4s; }
        .orb-3 { width: 200px; height: 200px; background: #06b6d4; top: 40%; left: 60%; animation-delay: -8s; }

        @keyframes drift {
            0%   { transform: translate(0, 0) scale(1); }
            100% { transform: translate(30px, 20px) scale(1.08); }
        }

        /* Glass card */
        .glass-login {
            background: rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Input focus glow */
        .input-field {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: white;
            transition: all 0.2s ease;
        }
        .input-field:focus {
            background: rgba(99, 102, 241, 0.12);
            border-color: rgba(99, 102, 241, 0.6);
            outline: none;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }
        .input-field::placeholder { color: rgba(255,255,255,0.3); }

        /* Submit button */
        .btn-submit {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            transition: all 0.2s ease;
        }
        .btn-submit:hover {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            transform: translateY(-1px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
        }
        .btn-submit:active { transform: translateY(0); }

        /* Logo gradient */
        .logo-text {
            background: linear-gradient(135deg, #818cf8, #38bdf8, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Slide-up animation */
        .card-enter {
            animation: cardEnter 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        }
        @keyframes cardEnter {
            from { opacity: 0; transform: translateY(24px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
    </style>
</head>

<body class="flex items-center justify-center min-h-screen p-4">

    <!-- Floating orbs -->
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="w-full max-w-md relative z-10 card-enter">

        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 mb-4 shadow-xl">
                <i class="fas fa-crown text-white text-xl"></i>
            </div>
            <h1 class="text-3xl font-bold logo-text">OwnStore</h1>
            <p class="text-slate-400 text-sm mt-1 tracking-widest uppercase">Command Center</p>
        </div>

        <!-- Card -->
        <div class="glass-login rounded-2xl shadow-2xl overflow-hidden">
            <div class="p-8">
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-white">Welcome back</h2>
                    <p class="text-slate-400 text-sm mt-1">Sign in to your super admin account</p>
                </div>

                @if ($errors->any())
                    <div class="mb-5 flex items-start p-4 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-300 text-sm">
                        <i class="fas fa-exclamation-circle mr-3 mt-0.5 flex-shrink-0"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('super.login.submit') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider" for="email">
                            Email Address
                        </label>
                        <div class="relative">
                            <i class="fas fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="admin@ownstore.com"
                                required
                                autofocus
                                class="input-field w-full pl-10 pr-4 py-3 rounded-xl text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider" for="password">
                            Password
                        </label>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                placeholder="••••••••"
                                required
                                class="input-field w-full pl-10 pr-4 py-3 rounded-xl text-sm">
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input
                            id="remember"
                            type="checkbox"
                            name="remember"
                            class="h-4 w-4 rounded bg-white/10 border border-white/20 text-indigo-500 focus:ring-indigo-500 cursor-pointer">
                        <label for="remember" class="ml-2 text-sm text-slate-400 cursor-pointer">
                            Remember me for 30 days
                        </label>
                    </div>

                    <button type="submit" class="btn-submit w-full py-3 rounded-xl text-white font-semibold text-sm tracking-wide">
                        Sign In to Command Center
                        <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </form>
            </div>

            <div class="bg-white/3 px-8 py-4 border-t border-white/5 text-center">
                <p class="text-xs text-slate-500">
                    <i class="fas fa-shield-alt mr-1.5 text-slate-600"></i>
                    Restricted Access — Authorized Personnel Only
                </p>
            </div>
        </div>
    </div>
</body>
</html>