<?php

namespace JordJD\DatesTimezoneConversion\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait DatesTimezoneConversion
{

    /**
     * Overrides getAttributeValue, and convert any dates
     * to the user's timezone.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttributeValue($key)
    {
        /** @var Carbon $value */
        $value = parent::getAttributeValue($key);

        if ($this->isDateObject($key, $value)) {

            /** @var Model $user */
            $user = Auth::user();

            if ($user) {
                $value->setTimezone($user->getAttributeValue('timezone'));
            }

        }

        return $value;
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->getDates())) {
            $value = $this->convertToDateObject($value);

            /** @var Model $user */
            $user = Auth::user();

            if ($user) {
                $value = Carbon::parse($value, $user->getAttributeValue('timezone'));
            }

            $value->setTimezone(config('app.timezone'));
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Checks if a date is part of the model's dates array,
     * is an object, and is a Carbon instance.
     *
     * @param $key
     * @param $value
     * @return bool
     */
    private function isDateObject($key, $value)
    {
        return in_array($key, $this->getDates()) &&
            is_object($value) &&
            $value instanceof Carbon;
    }

    /**
     * Converts a value to a Carbon date object if needed.
     *
     * @param $value
     * @return Carbon
     */
    private function convertToDateObject($value)
    {
        if (is_object($value) && $value instanceof Carbon) {
            return $value;
        }

        if (is_string($value)) {
            return Carbon::parse($value);
        }

        if (is_integer($value)) {
            return Carbon::createFromTimestamp($value);
        }

        throw new \Exception('Unable to convert value to Carbon date object.');
    }

}
