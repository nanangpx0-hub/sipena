<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MappingSchema extends Model
{
    use HasFactory;

    protected $fillable = [
        'opd_source_name',
        'table_identifier',
        'data_mode',
        'mapping_rules',
    ];

    protected $casts = [
        'mapping_rules' => 'array',
    ];
}
