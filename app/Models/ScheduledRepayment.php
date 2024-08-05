<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledRepayment extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_DUE = 'DUE';
    public const STATUS_PARTIAL = 'PARTIAL';
    public const STATUS_REPAID = 'REPAID';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'scheduled_repayments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $primaryKey = 'id'; 

    protected $fillable = [
        'loan_id', 'amount', 'outstanding_amount', 'currency_code', 'due_date', 'status', 'created_at', 'updated_at', 'deleted_at'
    ];
    
    protected $casts = [
        'amount' => 'decimal:2',
        'outstanding_amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    /**
     * A Scheduled Repayment belongs to a Loan
     *
     * @return BelongsTo
     */
    public function loan()
    {
        return $this->belongsTo(Loan::class, 'loan_id');
    }
}
