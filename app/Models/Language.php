<?php

namespace App\Models;
use App\Models\Resume;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = ['resume_id', 'name', 'proficiency'];

    // اللغة تنتمي لسيرة ذاتية واحدة
    public function resume()
    {
        return $this->belongsTo(Resume::class);
    }
}
