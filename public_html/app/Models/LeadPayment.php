<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadPayment extends Model
{
    use HasFactory;
    protected $table = "lead_payment";
    
    protected $fillable = [
        'created_by',
        'user_id',
        'crm_user_id',
        'enrollment_id',
        'payment_amount',
        'payment_option',
		'payment_method',
        'payment_term',
		'due_date',
		'payment_date',
		'payment_status',
        'payment_remark',
        'payment_mode',
        'payment_reference',
        
           ];
}
