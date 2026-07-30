<?php

namespace App\Http\Controllers;

use App\Models\ItemType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class ItemTypeController extends Controller
{
    /**
     * Ensure item_types table exists for the active tenant connection.
     */
    protected function ensureTableExists()
    {
        if (!Schema::hasTable('item_types')) {
            Schema::create('item_types', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->timestamps();
            });

            DB::table('item_types')->insert([
                ['name' => 'Inventory', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Service', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Package', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    /**
     * Search item types by name.
     */
    public function search(Request $request)
    {
        $this->ensureTableExists();

        $query = trim($request->get('q', ''));

        if (strlen($query) > 0) {
            $itemTypes = ItemType::where('name', 'like', "%{$query}%")
                ->orderBy('name', 'asc')
                ->limit(30)
                ->get(['id', 'name']);
        } else {
            $itemTypes = ItemType::orderBy('name', 'asc')
                ->limit(30)
                ->get(['id', 'name']);
        }

        return response()->json($itemTypes);
    }

    /**
     * Store a new item type.
     */
    public function store(Request $request)
    {
        $this->ensureTableExists();

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $name = trim($request->name);

        $itemType = ItemType::firstOrCreate(
            ['name' => $name]
        );

        return response()->json($itemType, 201);
    }
}
