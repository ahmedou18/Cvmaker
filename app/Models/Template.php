<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Resume;

class Template extends Model
{
    // الحقول المسموح إضافتها أو تعديلها
    protected $fillable = [
        'name', 
        'slug', 
        'thumbnail', 
        'view_path', 
        'is_premium'
    ];

    // تحويل حقل is_premium إلى قيمة منطقية (True/False)
    protected $casts = [
        'is_premium' => 'boolean',
    ];

    // القالب الواحد يمكن أن يُستخدم في العديد من السير الذاتية
    public function resumes()
    {
        return $this->hasMany(Resume::class);
    }
}
