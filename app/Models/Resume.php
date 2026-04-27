<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Template;
use App\Models\PersonalDetail;
use App\Models\Experience;
use App\Models\Education;
use App\Models\Skill;
use App\Models\Language;
use App\Models\Reference;
use App\Models\Hobby;

class Resume extends Model
{
    // الحقول المسموح بتعبئتها
    protected $fillable = [
        'user_id', 'template_id', 'uuid', 'title', 'is_published', 'settings',
        'extra_sections', // ✨ أضفنا الحقل الجديد هنا لكي يقبل الحفظ
        'resume_language'
    ];

    // تحويل حقول الـ JSON إلى مصفوفة تلقائياً (Casting)
    protected $casts = [
        'settings' => 'array',
        'is_published' => 'boolean',
        'extra_sections' => 'array', // ✨ وأضفناه هنا لنتعامل معه كمصفوفة في القوالب
    ];
    // توليد UUID تلقائياً عند الإنشاء
    protected static function booted()
    {
        static::creating(function ($resume) {
            $resume->uuid = (string) Str::uuid();
        });
    }

    /* ---------------------------------------------------------
       العلاقات مع الجداول الأبوية (Parents)
    --------------------------------------------------------- */
    
    // السيرة الذاتية تنتمي لمستخدم واحد
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // السيرة الذاتية تستخدم قالباً واحداً
    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    /* ---------------------------------------------------------
       العلاقات مع جداول التفاصيل (Children)
    --------------------------------------------------------- */

    // السيرة لها تفاصيل شخصية واحدة (One-to-One)
    public function personalDetail()
    {
        return $this->hasOne(PersonalDetail::class);
    }

    // السيرة لها عدة خبرات (One-to-Many)
    public function experiences()
    {
        return $this->hasMany(Experience::class);
    }

    // السيرة لها عدة مؤهلات تعليمية (One-to-Many)
    public function educations()
    {
        return $this->hasMany(Education::class);
    }

    // السيرة لها عدة مهارات (One-to-Many)
    public function skills()
    {
        return $this->hasMany(Skill::class);
    }

    // السيرة لها عدة لغات (One-to-Many) - التي أضفناها مؤخراً
    public function languages()
    {
        return $this->hasMany(Language::class);
    }

public function hobbies(): HasMany
    {
        return $this->hasMany(Hobby::class)->orderBy('sort_order');
    }

public function references(): HasMany
    {
        return $this->hasMany(Reference::class)->orderBy('sort_order');
    }

    // داخل موديل Resume
public function decrementNameChanges()
{
    if ($this->name_changes_left > 0) {
        $this->decrement('name_changes_left');
        if ($this->name_changes_left == 0) {
            $this->update(['is_name_locked' => true]);
        }
    }
}
}
