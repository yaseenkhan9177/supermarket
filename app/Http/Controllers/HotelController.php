<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Kot;
use App\Models\KotItem;

class HotelController extends Controller
{
    public function index()
    {
        $rooms = Room::orderBy('room_no')->get();
        $kots = Kot::with('items')->where('status', 'Active')->orderByDesc('id')->get();
        return view('hotel.index', compact('rooms', 'kots'));
    }

    public function storeKot(Request $request)
    {
        $validated = $request->validate([
            'table_or_room' => 'required|string',
            'guest_name' => 'nullable|string',
            'items' => 'required|array',
            'items.*.name' => 'required|string',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $kot = Kot::create([
            'kot_no' => 'KOT-' . time(), // Simple generator
            'table_or_room' => $validated['table_or_room'],
            'guest_name' => $validated['guest_name'],
            'status' => 'Active',
        ]);

        foreach ($validated['items'] as $item) {
            $kot->items()->create([
                'item_name' => $item['name'],
                'qty' => $item['qty'],
                'price' => $item['price'],
            ]);
        }

        return redirect()->back()->with('success', 'KOT Created Successfully');
    }

    public function updateRoomStatus(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'status' => 'required|in:Available,Occupied,Cleaning,Maintenance',
        ]);

        $room = Room::findOrFail($validated['room_id']);
        $room->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', 'Room Status Updated');
    }

    public function printKot($id)
    {
        $kot = Kot::with('items')->findOrFail($id);
        return view('hotel.print_kot', compact('kot'));
    }

    public function printBill($id)
    {
        // For now, reusing the print view or we could make a specific bill view.
        // User asked for "Generates the final bill".
        // Let's assume a similar structure but marked as Bill.
        $kot = Kot::with('items')->findOrFail($id);

        // Calculate total
        $total = $kot->items->sum(function ($item) {
            return $item->qty * $item->price;
        });

        return view('hotel.print_kot', compact('kot', 'total')); // We can handle 'total' presence in view to distinguish
    }
}
