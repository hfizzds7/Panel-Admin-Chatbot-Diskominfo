<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgeBase extends Model
{
    protected $table = 'knowledge_bases';

    protected $fillable = [
        'url',
        'cluster',
        'topic',
        'content',
        'keywords',
        'description',
        'source_url',
    ];

    // Konversi otomatis kolom keywords dari Array <-> JSON
    protected $casts = [
        'keywords' => 'array',
    ];
}