<?php

namespace App\Models;
use App\Models\Resume;

use Illuminate\Database\Eloquent\Model;

class Reference extends Model
{
    protected $fillable = [
        'resume_id', 'full_name', 'job_title', 'company',
        'email', 'phone', 'notes', 'sort_order'
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function resume()
    {
        return $this->belongsTo(Resume::class);
    }
}