<?php

namespace Gabievi\Promocodes\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Promocode extends Model
{
    /**
     * Indicates if the model should be timestamped.
     * We want to know, when the code was used. So, we need updated_at field.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'reward',
        'is_used',
        'expired_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_used' => 'boolean',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'expired_at',
    ];


    /**
     * Promocode constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('promocodes.table', 'promocodes');
    }

    /**
     * Get the foreign model which owns the promocode.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function foreign()
    {
        $foreignModel = config('promocodes.foreign_model');

        if (!$foreignModel) return null;

        // get a class name in short form (without namespace)
        $foreignClass = substr($foreignModel, strrpos($foreignModel, '\\') + 1);

        return  $this->belongsTo($foreignModel,Str::snake($foreignClass) . '_id');
    }

    /**
     * Returns true, if this promo code is expired
     *
     * @return bool
     */
    public function isExpired()
    {
        // check with Carbon's isPast() method
        return $this->expired_at ? $this->expired_at->isPast() : false;
    }


    /**
     * Query builder to find promocode using code.
     *
     * @param $query
     * @param $code
     *
     * @return mixed
     */
    public function scopeByCode($query, $code)
    {
        return $query->where('code', $code);
    }

    /**
     * Query builder to find all not used promocodes.
     *
     * @param $query
     *
     * @return mixed
     */
    public function scopeFresh($query)
    {
        return $query->where('is_used', false);
    }
}
