<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BooksRented extends Model
{
    protected $table = 'books_rented';

    protected $fillable = [
        'u_id',
        'b_id',
        'issued_on',
        'returned_on'
    ];
}
