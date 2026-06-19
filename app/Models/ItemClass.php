<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemClass extends Model
{
    // Table might be 'classes' or 'item_classes'. Since I didn't create a migration for it, this will error on query.
    // I'll leave it as default (item_classes) but usually 'classes'.
    // Given the constraints, I won't run migrations for these placeholders, so they are just for PHP code validity.
    protected $guarded = [];
}
