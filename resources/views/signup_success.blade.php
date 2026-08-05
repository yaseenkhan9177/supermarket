<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Submitted — Mart Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col justify-center items-center p-4">

    <div class="w-full max-w-md bg-slate-800 border border-slate-700 rounded-2xl shadow-2xl p-8 text-center">
        <div class="w-16 h-16 bg-emerald-500/20 text-emerald-400 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-white mb-2">Request Received!</h1>
        <p class="text-slate-300 mb-6">
            Thanks for registering <span class="font-semibold text-indigo-400">{{ session('store_name', 'your store') }}</span> with Mart.
        </p>
        <div class="bg-slate-900/60 border border-slate-700/60 rounded-xl p-4 text-left text-sm text-slate-400 mb-6 space-y-2">
            <p>• Our team will review your application shortly.</p>
            <p>• Once approved, your store environment will be provisioned automatically.</p>
            <p>• We will contact you via email/phone regarding payment and access activation.</p>
        </div>

        <a href="/" class="inline-block bg-slate-700 hover:bg-slate-600 text-slate-200 font-medium py-2.5 px-6 rounded-xl transition text-sm">
            Return to Homepage
        </a>
    </div>

</body>
</html>
