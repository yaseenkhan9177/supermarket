<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Manager | OwnStore PRO</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        .animate-fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body class="bg-gray-900 font-sans text-gray-200" x-data="hotelManager()">

    <!-- Toast Notification (Simple Implementation) -->
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
        class="fixed top-5 right-5 bg-green-500 text-white px-6 py-3 rounded shadow-lg z-50 animate-fade-in">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
    </div>
    @endif

    <nav class="bg-white border-b border-gray-200 px-6 py-3 shadow-sm sticky top-0 z-40 mb-8">
        <div class="container mx-auto max-w-[1400px] flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-rose-600 flex items-center justify-center text-white shadow-md">
                    <i class="fas fa-concierge-bell text-lg"></i>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-xl font-extrabold text-gray-900 leading-none tracking-tight">
                        OwnStore <span class="text-rose-600">PRO</span>
                    </h1>
                    <span class="text-xs text-gray-500 font-medium mt-0.5">Hotel & Restaurant Management</span>
                </div>
            </div>
            <div>
                <a href="/dashboard" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-800 hover:bg-black text-white text-sm font-bold rounded-lg shadow-sm transition transform hover:scale-105">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 max-w-[1400px] pb-32">

        <!-- Tabs -->
        <div class="flex gap-4 mb-8 border-b border-gray-700 pb-1">
            <button @click="activeTab = 'rooms'" :class="activeTab === 'rooms' ? 'border-rose-500 text-rose-400' : 'border-transparent text-gray-500 hover:text-gray-300'" class="pb-3 border-b-2 font-bold px-4 transition flex items-center gap-2">
                <i class="fas fa-bed"></i> Front Desk (Rooms)
            </button>
            <button @click="activeTab = 'kitchen'" :class="activeTab === 'kitchen' ? 'border-rose-500 text-rose-400' : 'border-transparent text-gray-500 hover:text-gray-300'" class="pb-3 border-b-2 font-bold px-4 transition flex items-center gap-2">
                <i class="fas fa-utensils"></i> Restaurant (KOTs)
            </button>
        </div>

        <!-- Rooms Tab -->
        <div x-show="activeTab === 'rooms'" class="animate-fade-in">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-white">Room Status</h2>
                <div class="flex gap-2">
                    <!-- Filter buttons can be implemented later -->
                    <button class="bg-gray-800 px-4 py-2 rounded text-xs font-bold text-gray-400 border border-gray-700">Filter: All</button>
                    <!-- New Room Button (Optional, maybe for admin only) -->
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($rooms as $room)
                <div class="relative bg-gray-800 rounded-xl p-4 border border-gray-700 hover:border-rose-500 transition group cursor-pointer h-40 flex flex-col justify-between overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 
                        {{ $room->status === 'Available' ? 'bg-green-500' : '' }}
                        {{ $room->status === 'Occupied' ? 'bg-red-500' : '' }}
                        {{ $room->status === 'Cleaning' ? 'bg-yellow-500' : '' }}
                        {{ $room->status === 'Maintenance' ? 'bg-gray-500' : '' }}
                    "></div>

                    <div class="flex justify-between items-start">
                        <span class="text-2xl font-extrabold text-white">{{ $room->room_no }}</span>
                        <i class="fas fa-bed text-gray-600 group-hover:text-rose-500 transition"></i>
                    </div>

                    <div>
                        <p class="text-xs text-gray-400 uppercase font-bold">{{ $room->type }}</p>
                        <p class="text-sm font-medium mt-1 
                           {{ $room->status === 'Available' ? 'text-green-400' : '' }}
                           {{ $room->status === 'Occupied' ? 'text-red-400' : '' }}
                           {{ $room->status === 'Cleaning' ? 'text-yellow-400' : '' }}
                        ">
                            {{ $room->status }}
                        </p>
                    </div>

                    <!-- Actions Overlay -->
                    <div class="absolute inset-0 bg-gray-900/90 flex items-center justify-center opacity-0 group-hover:opacity-100 transition duration-200 gap-2 flex-col p-4 text-center">
                        <form action="/hotel/room/status" method="POST" class="w-full">
                            @csrf
                            <input type="hidden" name="room_id" value="{{ $room->id }}">

                            @if($room->status === 'Available')
                            <input type="hidden" name="status" value="Occupied">
                            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-bold text-sm mb-2">Check In</button>
                            <button type="button" @click="setRoomStatus({{ $room->id }}, 'Cleaning')" class="w-full bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded font-bold text-xs">Mark Dirty</button>
                            @elseif($room->status === 'Occupied')
                            <input type="hidden" name="status" value="Cleaning"> <!-- Usually goes to cleaning after checkout -->
                            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded font-bold text-sm">Check Out</button>
                            @elseif($room->status === 'Cleaning')
                            <input type="hidden" name="status" value="Available">
                            <button type="submit" class="w-full bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded font-bold text-sm">Mark Clean</button>
                            @else
                            <span class="text-gray-400 text-xs">Maintenance</span>
                            @endif
                        </form>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Hidden form for manual status overrides if needed via JS -->
            <form id="statusForm" action="/hotel/room/status" method="POST" class="hidden">
                @csrf
                <input type="hidden" name="room_id" id="status_room_id">
                <input type="hidden" name="status" id="status_status">
            </form>
        </div>

        <!-- Kitchen Tab -->
        <div x-show="activeTab === 'kitchen'" style="display: none;" class="animate-fade-in">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-white">Active KOTs</h2>
                <button @click="openKotModal = true" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded text-sm font-bold shadow transition transform hover:scale-105">
                    <i class="fas fa-plus mr-2"></i> New KOT
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($kots as $kot)
                <div class="bg-white rounded-xl overflow-hidden shadow-lg h-full flex flex-col">
                    <div class="bg-rose-50 p-3 border-b border-rose-100 flex justify-between items-center">
                        <span class="font-bold text-rose-800 text-sm">#{{ $kot->kot_no }}</span>
                        <span class="bg-rose-200 text-rose-800 text-[10px] px-2 py-1 rounded-full font-bold uppercase">{{ $kot->table_or_room }}</span>
                    </div>
                    <div class="p-4 flex-1 flex flex-col">
                        <p class="text-xs text-gray-500 mb-2 font-bold">GUEST: <span>{{ $kot->guest_name ?? 'Walk-in' }}</span></p>
                        <ul class="space-y-2 mb-4 flex-1">
                            @foreach($kot->items as $item)
                            <div class="flex justify-between text-sm border-b border-gray-100 pb-1 last:border-0">
                                <span class="text-gray-800"><span class="font-bold">{{ $item->qty }}x</span> {{ $item->item_name }}</span>
                                <span class="text-gray-600 font-mono">{{ number_format($item->price, 2) }}</span>
                            </div>
                            @endforeach
                        </ul>
                        <div class="flex gap-2 mt-auto">
                            <a href="/hotel/kot/{{ $kot->id }}/print" target="_blank" class="flex-1 bg-gray-50 text-gray-600 hover:bg-gray-100 py-2 rounded text-xs font-bold border border-gray-200 text-center">
                                <i class="fas fa-print mr-1"></i> Print
                            </a>
                            <a href="/hotel/kot/{{ $kot->id }}/bill" target="_blank" class="flex-1 bg-green-50 text-green-600 hover:bg-green-100 py-2 rounded text-xs font-bold border border-green-200 text-center">
                                <i class="fas fa-receipt mr-1"></i> Bill
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>

    <!-- New KOT Modal -->
    <div x-show="openKotModal" x-cloak class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4">
        <div @click.away="openKotModal = false" class="bg-gray-800 rounded-xl shadow-2xl w-full max-w-lg border border-gray-700">
            <div class="p-6 border-b border-gray-700 flex justify-between items-center">
                <h3 class="text-lg font-bold text-white">Create New KOT</h3>
                <button @click="openKotModal = false" class="text-gray-400 hover:text-white"><i class="fas fa-times"></i></button>
            </div>
            <form action="/hotel/kot/store" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 mb-1">Table / Room</label>
                        <input type="text" name="table_or_room" required placeholder="e.g. Table 5" class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-white focus:border-rose-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 mb-1">Guest Name</label>
                        <input type="text" name="guest_name" placeholder="Optional" class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-white focus:border-rose-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 mb-2">Order Items</label>
                    <div class="space-y-2">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="flex gap-2">
                                <input type="text" :name="'items['+index+'][name]'" x-model="item.name" placeholder="Item Name" required class="flex-[3] bg-gray-900 border border-gray-600 rounded p-2 text-white text-sm">
                                <input type="number" :name="'items['+index+'][qty]'" x-model="item.qty" placeholder="Qty" required class="flex-1 bg-gray-900 border border-gray-600 rounded p-2 text-white text-sm">
                                <input type="number" step="0.01" :name="'items['+index+'][price]'" x-model="item.price" placeholder="Price" required class="flex-1 bg-gray-900 border border-gray-600 rounded p-2 text-white text-sm">
                                <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-400 px-2"><i class="fas fa-trash"></i></button>
                            </div>
                        </template>
                    </div>
                    <button type="button" @click="addItem" class="mt-3 text-sm text-rose-400 hover:text-rose-300 font-bold flex items-center gap-1">
                        <i class="fas fa-plus-circle"></i> Add Item
                    </button>
                </div>

                <div class="pt-4 border-t border-gray-700 flex justify-end gap-2">
                    <button type="button" @click="openKotModal = false" class="px-4 py-2 rounded bg-gray-700 hover:bg-gray-600 text-white text-sm font-bold">Cancel</button>
                    <button type="submit" class="px-6 py-2 rounded bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold shadow-lg">create Order</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function hotelManager() {
            return {
                activeTab: 'rooms', // 'rooms' or 'kitchen'
                openKotModal: false,
                items: [{
                    name: '',
                    qty: 1,
                    price: ''
                }],
                addItem() {
                    this.items.push({
                        name: '',
                        qty: 1,
                        price: ''
                    });
                },
                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    }
                },
                setRoomStatus(id, status) {
                    document.getElementById('status_room_id').value = id;
                    document.getElementById('status_status').value = status;
                    document.getElementById('statusForm').submit();
                }
            }
        }
    </script>
</body>

</html>