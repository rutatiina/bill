<?php

namespace Rutatiina\Bill\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Rutatiina\Tenant\Scopes\TenantIdScope;

class Annex extends Model
{
    use LogsActivity;

    protected static $logName = 'Bill Annex';
    protected static $logFillable = true;
    protected static $logAttributes = ['*'];
    protected static $logAttributesToIgnore = ['updated_at'];
    protected static $logOnlyDirty = true;

    protected $connection = 'tenant';

    protected $table = 'rg_bill_annexes';

    protected $primaryKey = 'id';

    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

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

    public function remittance()
    {
        return $this->hasOne('Rutatiina\PaymentsMade\Models\PaymentsMade', 'id', 'model_id')->orderBy('id', 'asc');
    }

    public function debit_note()
    {
        return $this->hasOne($this->attributes['model'], 'model_id')->orderBy('id', 'asc');
    }

}
