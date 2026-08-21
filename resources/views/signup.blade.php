<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up — Start Your Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col justify-center items-center p-4">

    <div class="w-full max-w-lg bg-slate-800 border border-slate-700 rounded-2xl shadow-2xl p-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-indigo-400 bg-clip-text text-transparent">Mart Platform</h1>
            <p class="text-slate-400 text-sm mt-2">Onboard your store in seconds</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 p-4 rounded-xl text-sm mb-6">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('signup.submit') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Business Name</label>
                <input type="text" name="store_name" value="{{ old('store_name') }}" required 
                    class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-slate-100 focus:outline-none focus:border-indigo-500 transition"
                    placeholder="e.g. Acme Supermarket">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Store Address</label>
                <textarea name="store_address" rows="2"
                    class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-slate-100 focus:outline-none focus:border-indigo-500 transition resize-none"
                    placeholder="e.g. Shop #12, Commercial Area, Main Boulevard">{{ old('store_address') }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Desired Subdomain</label>
                <div class="flex items-center">
                    <input type="text" name="subdomain" value="{{ old('subdomain') }}" required 
                        class="w-full bg-slate-900 border border-slate-700 rounded-l-xl px-4 py-3 text-slate-100 focus:outline-none focus:border-indigo-500 transition"
                        placeholder="my-store">
                    <span class="bg-slate-700 border border-l-0 border-slate-700 text-slate-400 px-4 py-3 rounded-r-xl text-sm font-medium">.mart.com</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Owner Full Name</label>
                    <input type="text" name="owner_name" value="{{ old('owner_name') }}" required 
                        class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-slate-100 focus:outline-none focus:border-indigo-500 transition"
                        placeholder="John Doe">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Phone Number</label>
                    <input type="text" name="owner_phone" value="{{ old('owner_phone') }}" required 
                        class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-slate-100 focus:outline-none focus:border-indigo-500 transition"
                        placeholder="+1 234 567 890">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Email Address</label>
                <input type="email" name="owner_email" value="{{ old('owner_email') }}" required 
                    class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-slate-100 focus:outline-none focus:border-indigo-500 transition"
                    placeholder="john@example.com">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Password</label>
                    <input type="password" name="password" required 
                        class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-slate-100 focus:outline-none focus:border-indigo-500 transition"
                        placeholder="••••••••">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Confirm Password</label>
                    <input type="password" name="password_confirmation" required 
                        class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-slate-100 focus:outline-none focus:border-indigo-500 transition"
                        placeholder="••••••••">
                </div>
            </div>

            <button type="submit" 
                class="w-full mt-4 bg-gradient-to-r from-indigo-500 to-blue-600 hover:from-indigo-600 hover:to-blue-700 text-white font-semibold py-3.5 px-6 rounded-xl shadow-lg transition duration-200 cursor-pointer">
                Submit Registration Request
            </button>
        </form>

        <p class="text-center text-xs text-slate-500 mt-6">
            Already have an active store? <a href="/login" class="text-indigo-400 hover:underline">Log in here</a>
        </p>
    </div>

</body>
</html>
