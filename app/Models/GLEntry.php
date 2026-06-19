<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GLEntry extends Model
{
    use HasFactory;

    protected $table = 'gl_entries';
    protected $guarded = [];
}
