<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteConfiguration extends Model
{
    use HasFactory;
    protected $table = 'site_configurations';
    protected $fillable = [
        'logo',
        'site_title',
        'company_information',
    ];
}
