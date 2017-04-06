<?php

namespace Gabievi\Promocodes\Traits;

use Gabievi\Promocodes\Facades\Promocodes;
use Gabievi\Promocodes\Model\Promocode;
use Carbon\Carbon;

trait Rewardable
{
    /**
     * Create promocodes for current model.
     *
     * @param int  $amount
     * @param double $reward
     * @param Carbon $expired_at
     *
     * @return mixed
     */
    public function createCode($amount = 1, $reward = null, $expired_at = null)
    {
        $records = [];

        // loop though each promocodes required
        foreach (Promocodes::output($amount) as $code) {
            $records[] = new Promocode([
                'code'       => $code,
                'reward'     => $reward,
                'expired_at' => $expired_at,
            ]);
        }

        // check for insertion of record
        if ($this->promocodes()->saveMany($records)) {
            return collect($records);
        }

        return collect([]);
    }

    /**
     * Apply promocode for user and get callback.
     *
     * @param $code
     * @param $callback
     *
     * @return bool|float
     */
    public function applyCode($code, $callback = null)
    {
        $promocode = Promocode::byCode($code)->fresh()->first();

        // check if exists not used code
        if (!is_null($promocode)) {

            //
            if (!is_null($promocode->user) && $promocode->user->id !== $this->attributes['id']) {

                // callback function with false value
                if (is_callable($callback)) {
                    $callback(false);
                }

                return false;
            }

            if ($promocode->isExpired()) {
                // cannot apply code, because it's already expired

                if (is_callable($callback)) {
                    $callback(false);
                }

                return false;
            }

            // update promocode as it is used
            if ($promocode->update(['is_used' => true])) {

                // callback function with reward value
                if (is_callable($callback)) {
                    $callback($promocode->reward ?: true);
                }

                return $promocode->reward ?: true;
            }
        }

        // callback function with false value
        if (is_callable($callback)) {
            $callback(false);
        }

        return false;
    }
}
