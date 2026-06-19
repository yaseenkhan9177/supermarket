<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Item | OwnStore PRO</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-gray-100 font-sans text-gray-800 flex items-center justify-center min-h-screen">

    <div class="bg-white rounded-xl shadow-2xl border-t-8 border-red-600 w-full max-w-lg overflow-hidden animate-fade-in-up">

        <div class="bg-red-50 p-6 flex flex-col items-center border-b border-red-100">
            <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center text-red-600 mb-4 shadow-inner">
                <i class="fas fa-trash-alt text-3xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-red-700">Delete Confirmation</h2>
            <p class="text-red-500 text-sm font-medium mt-1">This action cannot be undone.</p>
        </div>

        <div class="p-8">
            <p class="text-gray-600 mb-6 text-center">
                You are about to permanently delete the following item:
            </p>

            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded bg-white border border-gray-200 flex items-center justify-center text-2xl shadow-sm">
                    <i class="{{ $item->icon ?? 'fas fa-file' }} {{ $item->type == 'folder' ? 'text-yellow-500' : 'text-teal-500' }}"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800 text-lg">{{ $item->name }}</h3>
                    <span class="text-xs font-bold uppercase tracking-wide px-2 py-0.5 rounded {{ $item->type == 'folder' ? 'bg-yellow-100 text-yellow-700' : 'bg-teal-100 text-teal-700' }}">
                        {{ $item->type == 'folder' ? 'Folder / Group' : 'Report' }}
                    </span>
                </div>
            </div>

            @if($item->type == 'folder')
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded text-sm text-red-700 mb-6">
                <div class="flex items-start gap-3">
                    <i class="fas fa-exclamation-triangle mt-0.5"></i>
                    <div>
                        <strong>Warning:</strong> This is a folder. Deleting it will also remove all
                        <strong>{{ $children_count ?? '0' }}</strong> items inside it.
                    </div>
                </div>
            </div>
            @endif

            <form action="/delete/destroy" method="POST" class="flex gap-4">
                @csrf
                <input type="hidden" name="id" value="{{ $item->id }}">

                <a href="/reports" class="flex-1 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold rounded-lg transition text-center">
                    Cancel
                </a>

                <button type="submit" class="flex-1 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg shadow-lg transition transform hover:-translate-y-0.5 flex justify-center items-center gap-2">
                    <i class="fas fa-trash"></i> Yes, Delete It
                </button>
            </form>
        </div>

    </div>

    <style>
        @keyframes fade-in-up {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fade-in-up 0.4s ease-out;
        }
    </style>
</body>

</html>