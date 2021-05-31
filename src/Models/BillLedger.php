<?php

namespace Rutatiina\Bill\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Scopes\TenantIdScope;

class BillLedger extends Model
{
    use LogsActivity;

    protected static $logName = 'TxnLedger';
    protected static $logFillable = true;
    protected static $logAttributes = ['*'];
    protected static $logAttributesToIgnore = ['updated_at'];
    protected static $logOnlyDirty = true;

    protected $connection = 'tenant';

    protected $table = 'rg_bill_ledgers';

    protected $primaryKey = 'id';

    protected $guarded = ['id'];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new TenantIdScope);
    }

    public function bill()
    {
        return $this->belongsTo('Rutatiina\Bill\Models\Bill', 'bill_id');
    }

}
