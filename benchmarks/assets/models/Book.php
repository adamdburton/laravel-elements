<?php

namespace Click\Elements\Benchmarks\Assets\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $casts = ['released' => 'integer'];

    protected $fillable = ['author_id', 'name', 'released'];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }
}
