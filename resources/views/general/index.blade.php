<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>General Settings | OwnStore PRO</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        /* Custom Range Slider Styling */
        input[type=range]::-webkit-slider-thumb {
            -webkit-appearance: none;
            height: 16px;
            width: 16px;
            border-radius: 50%;
            background: #2563eb;
            cursor: pointer;
            margin-top: -6px;
        }

        input[type=range]::-webkit-slider-runnable-track {
            width: 100%;
            height: 4px;
            cursor: pointer;
            background: #e2e8f0;
            border-radius: 2px;
        }
    </style>
</head>

<body class="bg-gray-50 font-sans text-gray-800" x-data="generalSettings()">

    <nav class="bg-white border-b border-gray-200 px-6 py-3 shadow-sm sticky top-0 z-50 mb-8">
        <div class="container mx-auto max-w-[1400px] flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white shadow-md">
                    <i class="fas fa-cogs text-lg"></i>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-xl font-extrabold text-gray-900 leading-none tracking-tight">
                        OwnStore <span class="text-blue-600">PRO</span>
                    </h1>
                    <span class="text-xs text-gray-500 font-medium mt-0.5">General Configuration</span>
                </div>
            </div>
            <div>
                <a href="/dashboard" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-bold rounded-lg transition">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 md:px-6 max-w-[1400px] pb-32">

        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
        @endif

        <form action="/general/store" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

                <div class="lg:col-span-4 space-y-6">
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 text-center relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-full h-2 bg-blue-500"></div>

                        <div class="relative w-32 h-32 mx-auto mb-6 group cursor-pointer">
                            <div class="w-full h-full rounded-full border-4 border-dashed border-gray-200 flex items-center justify-center overflow-hidden bg-gray-50 group-hover:border-blue-400 transition">
                                <template x-if="logoPreview">
                                    <img :src="logoPreview" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!logoPreview">
                                    <div class="text-gray-400 text-center">
                                        <i class="fas fa-camera text-2xl mb-1"></i>
                                        <span class="block text-[10px] uppercase font-bold">Upload</span>
                                    </div>
                                </template>
                            </div>
                            <input type="file" name="logo" @change="previewLogo" class="absolute inset-0 opacity-0 cursor-pointer">
                        </div>

                        <h2 class="text-xl font-bold text-gray-800 mb-6">Store Identity</h2>

                        <div class="space-y-4 text-left">
                            <div>
                                <label class="text-xs font-bold text-gray-400 uppercase">Business Name</label>
                                <input type="text" name="business_name" value="{{ $settings->business_name ?? '' }}" class="w-full border-b border-gray-300 focus:border-blue-500 outline-none py-2 font-bold text-gray-700 bg-transparent transition">
                            </div>

                            <div>
                                <label class="text-xs font-bold text-gray-400 uppercase">Address</label>
                                <textarea name="address" rows="3" class="w-full border border-gray-200 rounded-lg p-3 mt-1 text-sm focus:ring-2 focus:ring-blue-100 outline-none resize-none">{{ $settings->address ?? '' }}</textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-xs font-bold text-gray-400 uppercase">Phone</label>
                                    <input type="text" name="phone" value="{{ $settings->phone ?? '' }}" class="w-full border border-gray-200 rounded p-2 mt-1 text-sm">
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-gray-400 uppercase">Fax</label>
                                    <input type="text" name="fax" value="{{ $settings->fax ?? '' }}" class="w-full border border-gray-200 rounded p-2 mt-1 text-sm">
                                </div>
                            </div>

                            <div>
                                <label class="text-xs font-bold text-gray-400 uppercase">Email</label>
                                <input type="email" name="email" value="{{ $settings->email ?? '' }}" class="w-full border border-gray-200 rounded p-2 mt-1 text-sm">
                            </div>

                            <div>
                                <label class="text-xs font-bold text-gray-400 uppercase">Website</label>
                                <input type="text" name="website" value="{{ $settings->website ?? '' }}" class="w-full border border-gray-200 rounded p-2 mt-1 text-sm text-blue-600">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-8 space-y-6">

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                        <h3 class="font-bold text-gray-800 text-lg mb-6 flex items-center gap-2">
                            <i class="fas fa-print text-blue-500"></i> Printers & Hardware
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Receipt Printer</label>
                                <div class="relative">
                                    <i class="fas fa-receipt absolute left-3 top-3 text-gray-400"></i>
                                    <select name="receipt_printer" class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2.5 focus:border-blue-500 outline-none bg-white">
                                        <option value="">-- Select Printer --</option>
                                        <option value="Thermal POS-80" {{ ($settings->receipt_printer ?? '') == 'Thermal POS-80' ? 'selected' : '' }}>Thermal POS-80</option>
                                        <option value="Microsoft Print to PDF" {{ ($settings->receipt_printer ?? '') == 'Microsoft Print to PDF' ? 'selected' : '' }}>Microsoft Print to PDF</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Barcode Printer</label>
                                <div class="relative">
                                    <i class="fas fa-barcode absolute left-3 top-3 text-gray-400"></i>
                                    <select name="barcode_printer" class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2.5 focus:border-blue-500 outline-none bg-white">
                                        <option value="">-- Select Printer --</option>
                                        <option value="Zebra ZD420" {{ ($settings->barcode_printer ?? '') == 'Zebra ZD420' ? 'selected' : '' }}>Zebra ZD420</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                        <h3 class="font-bold text-gray-800 text-lg mb-6 flex items-center gap-2">
                            <i class="fas fa-hdd text-purple-500"></i> POS Hardware & COMM Ports
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Cash Drawer Name</label>
                                <input type="text" name="cash_drawer_name" value="{{ $settings->cash_drawer_name ?? '' }}" class="w-full bg-white border border-gray-200 rounded p-2 text-sm mb-3">

                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Attached to COMM</label>
                                <input type="text" name="cash_drawer_port" value="{{ $settings->cash_drawer_port ?? '8' }}" class="w-full bg-white border border-gray-200 rounded p-2 text-sm">
                            </div>

                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Customer Display</label>
                                <input type="text" name="customer_display_name" value="{{ $settings->customer_display_name ?? '' }}" class="w-full bg-white border border-gray-200 rounded p-2 text-sm mb-3">

                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Attached to COMM</label>
                                <input type="text" name="customer_display_port" value="{{ $settings->customer_display_port ?? '32' }}" class="w-full bg-white border border-gray-200 rounded p-2 text-sm">
                            </div>

                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">POS System Name</label>
                                <input type="text" name="pos_system_name" value="{{ $settings->pos_system_name ?? 'Counter 1' }}" class="w-full bg-white border border-gray-200 rounded p-2 text-sm font-bold text-gray-700">
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                        <h3 class="font-bold text-gray-800 text-lg mb-6 flex items-center gap-2">
                            <i class="fas fa-globe text-green-500"></i> Regional & Layout Configuration
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">FBR POSTID (Tax)</label>
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-university text-gray-400"></i>
                                        <input type="text" name="fbr_post_id" value="{{ $settings->fbr_post_id ?? '' }}" placeholder="Required for FBR integration" class="w-full border border-gray-300 rounded p-2 text-sm">
                                    </div>
                                </div>
                                <div class="flex items-end gap-4">
                                    <div class="flex-1">
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Currency</label>
                                        <input type="text" name="currency_symbol" value="{{ $settings->currency_symbol ?? 'Rs' }}" class="w-full border border-gray-300 rounded p-2 text-sm">
                                    </div>
                                    <label class="flex items-center gap-2 cursor-pointer pb-2">
                                        <input type="checkbox" name="outlook_check" class="w-5 h-5 rounded text-blue-600 focus:ring-blue-500 border-gray-300" {{ ($settings->outlook_check ?? false) ? 'checked' : '' }}>
                                        <span class="text-sm font-medium text-gray-700">Outlook Check</span>
                                    </label>
                                </div>
                            </div>

                            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                                <h4 class="text-xs font-bold text-gray-400 uppercase mb-3">Printing Layout</h4>

                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="block text-[10px] text-gray-500 mb-1">Barcode Rows</label>
                                        <input type="number" name="barcode_rows" value="{{ $settings->barcode_rows ?? 11 }}" class="w-full border rounded p-1.5 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] text-gray-500 mb-1">Barcode Cols</label>
                                        <input type="number" name="barcode_cols" value="{{ $settings->barcode_cols ?? 4 }}" class="w-full border rounded p-1.5 text-sm">
                                    </div>
                                </div>

                                <div>
                                    <div class="flex justify-between text-[10px] text-gray-500 mb-1">
                                        <span>Receipt Width</span>
                                        <span class="font-bold text-blue-600" x-text="receiptWidth + 'px'"></span>
                                    </div>
                                    <input type="range" name="receipt_width" x-model="receiptWidth" min="200" max="400" class="w-full h-1 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                                    <div class="flex justify-between text-[9px] text-gray-400 mt-1">
                                        <span>200px</span>
                                        <span>400px</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="fixed bottom-0 left-0 w-full bg-white border-t p-4 shadow-[0_-5px_15px_rgba(0,0,0,0.1)] z-40">
                <div class="container mx-auto max-w-[1400px] flex justify-end gap-4">
                    <button type="reset" class="px-6 py-3 bg-gray-100 text-gray-600 font-bold rounded hover:bg-gray-200 transition">Reset Defaults</button>

                    <button type="submit" class="px-8 py-3 bg-blue-600 text-white font-bold rounded shadow-lg hover:bg-blue-700 transition transform hover:-translate-y-1 flex items-center gap-2">
                        <i class="fas fa-save"></i> Save Configuration
                    </button>
                </div>
            </div>

        </form>
    </div>

    <script>
        function generalSettings() {
            return {
                receiptWidth: {
                    {
                        $settings - > receipt_width ?? 300
                    }
                },
                logoPreview: {
                    {
                        \
                        Illuminate\ Support\ Js::from($settings - > logo_path ? asset("storage/".$settings - > logo_path) : "")
                    }
                },

                previewLogo(event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.logoPreview = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                }
            }
        }
    </script>
</body>

</html>