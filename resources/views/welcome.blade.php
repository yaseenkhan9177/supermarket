<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>OwnStore – Smart Retail System</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 text-gray-800">

    <!-- NAVBAR -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <a href="#" class="flex items-center gap-2">
                <img src="{{ asset('images/logo.png') }}" alt="OwnStore Logo" class="h-10 w-10 rounded-full">
                <span class="text-2xl font-bold text-indigo-600">OwnStore</span>
            </a>
            <nav class="space-x-6 hidden md:block">
                <a href="#" class="hover:text-indigo-600">Features</a>
                <a href="#" class="hover:text-indigo-600">Business Types</a>
                <a href="#" class="hover:text-indigo-600">Pricing</a>
                <a href="{{ route('login') }}" class="hover:text-indigo-600">Login</a>
            </nav>
            <a href="{{ route('store.register.form') }}" class="bg-indigo-600 text-white px-5 py-2 rounded-lg hover:bg-indigo-700">
                Get Started
            </a>
        </div>
    </header>

    <!-- HERO SECTION -->
    <section class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
        <div class="max-w-7xl mx-auto px-6 py-24 grid md:grid-cols-2 gap-12 items-center">
            <div>
                <h2 class="text-4xl md:text-5xl font-extrabold leading-tight">
                    One Smart System <br> For Every Store
                </h2>
                <p class="mt-6 text-lg text-indigo-100">
                    Manage your Super Market, Medical Store, Shoe Shop, and more —
                    all from one powerful & easy-to-use platform.
                </p>
                <div class="mt-8 flex gap-4">
                    <a href="{{ route('store.register.form') }}" class="bg-white text-indigo-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100">
                        Start Free
                    </a>
                    <a href="#" class="border border-white px-6 py-3 rounded-lg hover:bg-white hover:text-indigo-600">
                        Watch Demo
                    </a>
                </div>
            </div>

            <div class="hidden md:block">
                <img src="https://illustrations.popsy.co/gray/sales.svg" alt="Dashboard" class="w-full">
            </div>
        </div>
    </section>

    <!-- BUSINESS TYPES -->
    <section class="max-w-7xl mx-auto px-6 py-20">
        <h3 class="text-3xl font-bold text-center mb-12">
            Built for Every Type of Business
        </h3>

        <div class="grid md:grid-cols-4 gap-8">
            <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
                <h4 class="text-xl font-semibold mb-2">🛒 Super Market</h4>
                <p class="text-gray-600">Barcode billing, stock & supplier management.</p>
            </div>

            <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
                <h4 class="text-xl font-semibold mb-2">💊 Medical Store</h4>
                <p class="text-gray-600">Expiry alerts, batch tracking & safe sales.</p>
            </div>

            <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
                <h4 class="text-xl font-semibold mb-2">👟 Shoe Shop</h4>
                <p class="text-gray-600">Size, color variants & brand control.</p>
            </div>

            <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
                <h4 class="text-xl font-semibold mb-2">🏪 Retail Stores</h4>
                <p class="text-gray-600">Flexible setup for any type of shop.</p>
            </div>
        </div>
    </section>

    <!-- FEATURES -->
    <section class="bg-white py-20">
        <div class="max-w-7xl mx-auto px-6">
            <h3 class="text-3xl font-bold text-center mb-12">
                Powerful Features You’ll Love
            </h3>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="p-6 border rounded-xl">
                    <h4 class="font-semibold text-lg mb-2">⚡ Fast POS</h4>
                    <p class="text-gray-600">Quick billing with keyboard & barcode support.</p>
                </div>
                <div class="p-6 border rounded-xl">
                    <h4 class="font-semibold text-lg mb-2">📦 Inventory</h4>
                    <p class="text-gray-600">Real-time stock, low stock & expiry alerts.</p>
                </div>
                <div class="p-6 border rounded-xl">
                    <h4 class="font-semibold text-lg mb-2">📊 Reports</h4>
                    <p class="text-gray-600">Sales, profit & performance insights.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="bg-indigo-600 text-white py-20 text-center">
        <h3 class="text-4xl font-bold mb-6">
            Ready to Run Your Store Smarter?
        </h3>
        <a href="{{ route('store.register.form') }}" class="bg-white text-indigo-600 px-8 py-4 rounded-lg font-semibold hover:bg-gray-100">
            Create Your Store Now
        </a>
        <p class="mt-4 text-indigo-200">Setup takes less than 10 minutes</p>
    </section>

    <!-- FOOTER -->
    <footer class="bg-gray-900 text-gray-400 py-6 text-center">
        © 2026 OwnStore. All rights reserved.
    </footer>

</body>

</html>