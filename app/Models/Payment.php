<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id', 
        'plan_id', 
        'transaction_reference', 
        'amount', 
        'status',
        'payment_method',
        'screenshot_path',
        'admin_notes',
        'moosyl_transaction_id',
        'currency'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
