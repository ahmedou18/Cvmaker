<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Resume;

class Skill extends Model
{
    protected $fillable = [
        'resume_id', 'name', 'percentage', 'level', 'sort_order'
    ];

    protected $casts = [
        'percentage' => 'integer',
        'sort_order' => 'integer',
    ];

    public function resume()
    {
        return $this->belongsTo(Resume::class);
    }
}