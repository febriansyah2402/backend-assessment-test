<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use HasFactory, SoftDeletes;

    const CURRENCY_VND = 'VND';
    const STATUS_DUE = 'DUE';
    const STATUS_REPAID = 'REPAID';

    protected $primaryKey = 'id'; 

    protected $fillable = [
        'user_id', 'amount', 'currency_code', 'terms', 'processed_at', 'status', 'outstanding_amount', 'created_at', 'updated_at', 'deleted_at'
    ];

    public function scheduledRepayments()
    {
        return $this->hasMany(ScheduledRepayment::class);
    }

    public function receivedRepayments()
    {
        return $this->hasMany(ReceivedRepayment::class);
    }
}
