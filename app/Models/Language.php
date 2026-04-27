<?php

namespace App\Models;
use App\Models\Resume;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = [
        'resume_id', 'name', 'proficiency', 'level', 'percentage', 'sort_order'
    ];

    protected $casts = [
        'level' => 'integer',
        'percentage' => 'integer',
        'sort_order' => 'integer',
    ];

    public function resume()
    {
        return $this->belongsTo(Resume::class);
    }
}