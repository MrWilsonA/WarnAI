<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkspaceProject extends Model
{
    protected $fillable = [
        'name',
        'repository_path',
        'files_count',
        'chunks_count',
        'raw_size_bytes',
        'clean_size_bytes',
        'estimated_tokens',
        'token_savings_pct',
        'status',
    ];

    protected $casts = [
        'files_count' => 'integer',
        'chunks_count' => 'integer',
        'raw_size_bytes' => 'integer',
        'clean_size_bytes' => 'integer',
        'estimated_tokens' => 'integer',
        'token_savings_pct' => 'float',
    ];
}
