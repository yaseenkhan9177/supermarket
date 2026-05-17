<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Register Your Store | OwnStore</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-indigo-600 to-purple-600 min-h-screen flex items-center justify-center">

    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl overflow-hidden grid md:grid-cols-2">

        <!-- LEFT INFO -->
        <div class="bg-indigo-600 text-white p-10 hidden md:block">
            <h2 class="text-3xl font-bold mb-4">OwnStore</h2>
            <p class="text-indigo-100 mb-6">
                Register your store and start managing sales, inventory, and reports
                from one powerful dashboard.
            </p>

            <ul class="space-y-3 text-indigo-100">
                <li>✔ Fast POS Billing</li>
                <li>✔ Inventory & Stock Alerts</li>
                <li>✔ Medical, Supermarket, Retail Support</li>
                <li>✔ Secure & Reliable</li>
            </ul>
        </div>

        <!-- FORM -->
        <div class="p-10">
            <h3 class="text-2xl font-bold mb-6 text-gray-800">
                Create Your Store Account
            </h3>

            <form class="space-y-5" method="POST" action="{{ route('store.register') }}">
                @csrf

                @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- OWNER INFO -->
                <div>
                    <label class="block text-sm font-medium">Owner Name</label>
                    <input type="text" name="owner_name" value="{{ old('owner_name') }}" placeholder="Muhammad Yaseen"
                        class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="owner@email.com"
                        class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+92 3xx xxxxxxx"
                        class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>

                <!-- STORE INFO -->
                <div>
                    <label class="block text-sm font-medium">Store Name</label>
                    <input type="text" name="store_name" value="{{ old('store_name') }}" placeholder="ABC Medical Store"
                        class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium">Business Type</label>
                    <select name="business_type" class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select Business Type</option>
                        <option value="Super Market" {{ old('business_type') == 'Super Market' ? 'selected' : '' }}>Super Market</option>
                        <option value="Medical Store" {{ old('business_type') == 'Medical Store' ? 'selected' : '' }}>Medical Store</option>
                        <option value="Shoe Shop" {{ old('business_type') == 'Shoe Shop' ? 'selected' : '' }}>Shoe Shop</option>
                        <option value="Retail Store" {{ old('business_type') == 'Retail Store' ? 'selected' : '' }}>Retail Store</option>
                    </select>
                </div>

                <!-- PASSWORD -->
                <div>
                    <label class="block text-sm font-medium">Password</label>
                    <input type="password" name="password"
                        class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium">Confirm Password</label>
                    <input type="password" name="password_confirmation"
                        class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>

                <!-- SUBMIT -->
                <button type="submit"
                    class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700">
                    Register Store
                </button>

                <p class="text-sm text-center text-gray-600">
                    Already have an account?
                    <a href="#" class="text-indigo-600 font-semibold">Login</a>
                </p>

            </form>
        </div>
    </div>

</body>

</html>