<?php

namespace Click\Elements\Benchmarks\Assets\Models;

use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    protected $casts = ['born' => 'integer'];

    protected $fillable = ['name', 'born'];

    public function books()
    {
        return $this->hasMany(Book::class);
    }
}
