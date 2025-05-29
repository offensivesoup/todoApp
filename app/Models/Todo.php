<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    use HasFactory;

    protected $table = 'tbl_todo';

    protected $fillable = ['todo_text', 'is_complete', 'priority'];

    protected $hidden = ['updated_at', 'deleted_at'];
}
