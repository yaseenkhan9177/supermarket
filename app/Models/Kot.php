<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kot extends Model
{
    use HasFactory;

    protected $fillable = ['kot_no', 'table_or_room', 'guest_name', 'status'];

    public function items()
    {
        return $this->hasMany(KotItem::class);
    }
}
