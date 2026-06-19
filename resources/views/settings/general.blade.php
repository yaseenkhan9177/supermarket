<!DOCTYPE html>
<html lang="en">

<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100 font-sans text-gray-800">

    <div class="container mx-auto p-6 max-w-7xl">

        @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: "{{ session('success') }}",
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                    toast: true,
                    position: 'top-end'
                });
            });
        </script>
        @endif

        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">General Settings</h1>
                <p class="text-gray-500 mt-1">Manage store identity, FBR integration, and POS hardware.</p>
            </div>
            <div class="space-x-2">
                <a href="/admin" class="px-4 py-2 bg-white border rounded shadow-sm hover:bg-gray-50 text-gray-700 inline-block">
                    <i class="fas fa-home"></i>
                </a>
                <button class="px-4 py-2 bg-white border rounded shadow-sm hover:bg-gray-50 text-gray-700">View Mode</button>
                <button type="submit" form="settingsForm" class="px-4 py-2 bg-indigo-600 text-white rounded shadow hover:bg-indigo-700 transition">
                    <i class="fas fa-save mr-2"></i> Save Changes
                </button>
            </div>
        </div>

        <form id="settingsForm" action="/settings/update" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

                <div class="lg:col-span-4 space-y-6">
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                        <div class="text-center mb-6">
                            <div class="relative w-32 h-32 mx-auto mb-4 group">
                                <div class="w-full h-full rounded-full bg-gray-50 border-2 border-dashed border-gray-300 flex items-center justify-center overflow-hidden">
                                    <img id="logoPreview" src="{{ $settings->logo_path ? asset('storage/'.$settings->logo_path) : '/path/to/default-logo.png' }}" class="object-cover w-full h-full {{ $settings->logo_path ? '' : 'opacity-50' }}" alt="Logo">
                                    <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-400">
                                        <i class="fas fa-camera text-2xl mb-1"></i>
                                        <span class="text-xs">Upload</span>
                                    </div>
                                </div>
                                <input type="file" name="logo" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full" onchange="document.getElementById('logoPreview').src = window.URL.createObjectURL(this.files[0]); document.getElementById('logoPreview').classList.remove('opacity-50')">
                            </div>
                            <h2 class="text-xl font-bold text-gray-800">Store Identity</h2>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-600 mb-1">Business Name</label>
                                <input type="text" name="business_name" value="{{ $settings->business_name }}" class="w-full rounded-lg border-gray-300 border p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-600 mb-1">Address</label>
                                <textarea name="address" rows="3" class="w-full rounded-lg border-gray-300 border p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">{{ $settings->address }}</textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Phone</label>
                                    <input type="text" name="phone" value="{{ $settings->phone }}" class="w-full rounded border-gray-300 border p-2 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Fax</label>
                                    <input type="text" name="fax" value="{{ $settings->fax }}" class="w-full rounded border-gray-300 border p-2 text-sm">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-600 mb-1">Email</label>
                                <input type="email" name="email" value="{{ $settings->email }}" class="w-full rounded-lg border-gray-300 border p-2.5">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-600 mb-1">Website</label>
                                <input type="text" name="website" value="{{ $settings->website }}" class="w-full rounded-lg border-gray-300 border p-2.5">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-8 space-y-6">

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-4 bg-gray-50 border-b flex items-center justify-between">
                            <h3 class="font-bold text-gray-800 flex items-center">
                                <i class="fas fa-print w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center mr-3"></i>
                                Printers & Hardware
                            </h3>
                        </div>

                        <div class="p-6 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Receipt Printer</label>
                                    <select name="printer_receipt" class="w-full p-2.5 bg-white border border-gray-300 rounded-lg">
                                        <option value="Microsoft Print to PDF" {{ $settings->printer_receipt == 'Microsoft Print to PDF' ? 'selected' : '' }}>Microsoft Print to PDF</option>
                                        <option value="Thermal POS-80" {{ $settings->printer_receipt == 'Thermal POS-80' ? 'selected' : '' }}>Thermal POS-80</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Barcode Printer</label>
                                    <select name="printer_barcode" class="w-full p-2.5 bg-white border border-gray-300 rounded-lg">
                                        <option value="">Select Printer...</option>
                                        <!-- Add more options as needed -->
                                    </select>
                                </div>
                            </div>

                            <hr class="border-gray-100">

                            <div>
                                <h4 class="text-sm font-bold text-gray-900 mb-3">POS Hardware Names & COMM Ports</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="bg-gray-50 p-3 rounded border">
                                        <label class="block text-xs text-gray-500 mb-1">Cash Drawer Name</label>
                                        <input type="text" name="pos_drawer_name" value="{{ $settings->pos_drawer_name }}" class="w-full p-1 bg-white border rounded text-sm mb-2">
                                        <label class="block text-xs text-gray-500 mb-1">Attached to COMM:</label>
                                        <input type="number" name="comm_port_drawer" value="{{ $settings->comm_port_drawer }}" class="w-full p-1 bg-white border rounded text-sm">
                                    </div>
                                    <div class="bg-gray-50 p-3 rounded border">
                                        <label class="block text-xs text-gray-500 mb-1">Customer Display</label>
                                        <input type="text" name="pos_display_name" value="{{ $settings->pos_display_name }}" class="w-full p-1 bg-white border rounded text-sm mb-2">
                                        <label class="block text-xs text-gray-500 mb-1">Attached to COMM:</label>
                                        <input type="number" name="comm_port_display" value="{{ $settings->comm_port_display }}" class="w-full p-1 bg-white border rounded text-sm">
                                    </div>
                                    <div class="bg-gray-50 p-3 rounded border">
                                        <label class="block text-xs text-gray-500 mb-1">POS System Name</label>
                                        <input type="text" name="pos_printer_name" value="{{ $settings->pos_printer_name }}" class="w-full p-1 bg-white border rounded text-sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-4 bg-gray-50 border-b flex items-center justify-between">
                            <h3 class="font-bold text-gray-800 flex items-center">
                                <i class="fas fa-globe w-8 h-8 rounded-lg bg-green-100 text-green-600 flex items-center justify-center mr-3"></i>
                                Regional & Layout Configuration
                            </h3>
                        </div>

                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700">FBR POSTID (Tax)</label>
                                    <div class="flex mt-1">
                                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                            <i class="fas fa-landmark"></i>
                                        </span>
                                        <input type="text" name="fbr_post_id" value="{{ $settings->fbr_post_id }}" placeholder="HOME" class="flex-1 block w-full rounded-none rounded-r-md border-gray-300 border p-2 focus:ring-indigo-500">
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Required for FBR integration in Pakistan.</p>
                                </div>

                                <div class="flex gap-4">
                                    <div class="flex-1">
                                        <label class="block text-sm font-semibold text-gray-700">Currency</label>
                                        <input type="text" name="currency_symbol" value="{{ $settings->currency_symbol }}" class="mt-1 block w-full rounded-md border-gray-300 border p-2 shadow-sm">
                                    </div>
                                    <div class="flex items-center pt-6">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="outlook_integration" class="form-checkbox h-5 w-5 text-indigo-600 rounded" {{ $settings->outlook_integration ? 'checked' : '' }}>
                                            <span class="ml-2 text-sm text-gray-700">Outlook Check</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <h4 class="text-xs font-bold text-gray-500 uppercase mb-3 border-b pb-2">Printing Layout</h4>

                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Barcode Rows</label>
                                        <input type="number" name="barcode_labels_per_row" value="{{ $settings->barcode_labels_per_row }}" class="w-full p-1.5 border rounded">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Barcode Cols</label>
                                        <input type="number" name="barcode_labels_per_col" value="{{ $settings->barcode_labels_per_col }}" class="w-full p-1.5 border rounded">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Receipt Width (200-400)</label>
                                    <input type="range" name="receipt_width" min="200" max="400" value="{{ $settings->receipt_width }}" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer" oninput="document.getElementById('width-val').textContent = this.value + 'px'">
                                    <div class="flex justify-between text-xs text-gray-400 mt-1">
                                        <span>200px</span>
                                        <span id="width-val">{{ $settings->receipt_width }}px</span>
                                        <span>400px</span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </form>
    </div>

</body>

</html>