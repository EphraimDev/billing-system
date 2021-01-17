<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'amount',
        'date_collected',
        'date_due',
        'duration',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\user');
    }

    public function bill()
    {
        return $this->hasOne('App\Models\Bill');
    }
}
