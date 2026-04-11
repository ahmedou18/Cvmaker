<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Resume;
use App\Models\AiGenerationHistory;

class PersonalDetail extends Model
{
    protected $fillable = [
        'resume_id', 
        'full_name', 
        'job_title', 
        'email', 
        'phone', 
        'address', 
        'summary', 
        'photo_path'
    ];

    // هذه التفاصيل تعود لسيرة ذاتية واحدة
    public function resume()
    {
        return $this->belongsTo(Resume::class);
    }

    public function aiGenerations()
{
    return $this->morphMany(AiGenerationHistory::class, 'generateable');
}
}