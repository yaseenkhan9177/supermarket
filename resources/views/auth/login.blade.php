<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Ensure viewport is set -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login | OwnStore</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-indigo-600 to-purple-600 min-h-screen flex items-center justify-center p-4"> <!-- Added p-4 for mobile spacing -->

    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl overflow-hidden grid md:grid-cols-2">

        <!-- LEFT INFO (Hidden on Mobile) -->
        <div class="bg-indigo-600 text-white p-8 md:p-10 hidden md:flex flex-col justify-center">
            <h2 class="text-3xl font-bold mb-4">OwnStore</h2>
            <p class="text-indigo-100 mb-6">
                Welcome back! Log in to access your dashboard.
            </p>
            <ul class="space-y-3 text-indigo-100">
                <li>✔ Super Admin Command Center</li>
                <li>✔ Store Management</li>
                <li>✔ Employee Portal</li>
            </ul>
        </div>

        <!-- FORM -->
        <div class="p-6 md:p-10 flex flex-col justify-center">
            <div class="text-center md:text-left">
                <!-- Mobile Logo -->
                <h2 class="text-2xl font-bold text-indigo-600 mb-2 md:hidden">OwnStore</h2>
                <h3 class="text-xl md:text-2xl font-bold mb-6 text-gray-800">
                    Login to Account
                </h3>
            </div>

            <form class="space-y-4 md:space-y-5" method="POST" action="{{ route('login') }}">
                @csrf

                @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative text-sm" role="alert">
                    <ul class="list-disc pl-4">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- EMAIL -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="name@company.com"
                        class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition-shadow">
                </div>

                <!-- PASSWORD -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="password" placeholder="********"
                        class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition-shadow">
                </div>

                <!-- SUBMIT -->
                <button type="submit"
                    class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 active:bg-indigo-800 transition-colors">
                    Login
                </button>

                <p class="text-sm text-center text-gray-600">
                    Don't have an account?
                    <a href="{{ route('store.register.form') }}" class="text-indigo-600 font-semibold hover:underline">Register Store</a>
                </p>

            </form>
        </div>
    </div>

</body>

</html>