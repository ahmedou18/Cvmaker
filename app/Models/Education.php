<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Resume;

class Education extends Model
{
    protected $fillable = [
        'resume_id', 
        'institution', 
        'degree', 
        'field_of_study', 
        'graduation_year',
        'description'
    ];

    // تحويل سنة التخرج إلى تاريخ (إذا استخدمت نوع Date في الميغراسيون)
    protected $casts = [
        //'graduation_year' => 'date',
    ];

    public function resume()
    {
        return $this->belongsTo(Resume::class);
    }
    public function aiGenerations()
{
    return $this->morphMany(AiGenerationHistory::class, 'generateable');
}
}