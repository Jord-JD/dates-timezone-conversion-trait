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

            $timezone = $user ? $user->getAttributeValue('timezone') : null;
            if (is_string($timezone) && $timezone !== '') {
                $value = clone $value;
                $value->setTimezone($timezone);
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
        if ($this->isDateAttribute($key) && $value !== null && $value !== '') {

            /** @var Model $user */
            $user = Auth::user();

            $timezone = $user ? $user->getAttributeValue('timezone') : null;
            $timezone = is_string($timezone) && $timezone !== '' ? $timezone : null;
            $value = $this->convertToDateObject($value, $timezone);

            $applicationTimezone = config('app.timezone');
            $value->setTimezone($applicationTimezone ?: date_default_timezone_get());
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
        return $this->isDateAttribute($key) &&
            is_object($value) &&
            $value instanceof Carbon;
    }

    /**
     * Converts a value to a Carbon date object if needed.
     *
     * @param $value
     * @return Carbon
     */
    private function convertToDateObject($value, $timezone = null)
    {
        if (is_object($value) && $value instanceof Carbon) {
            return clone $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }

        if (is_string($value)) {
            return Carbon::parse($value, $timezone);
        }

        if (is_integer($value)) {
            return Carbon::createFromTimestamp($value);
        }

        throw new \InvalidArgumentException('Unable to convert value to Carbon date object.');
    }

    /**
     * Determine whether an attribute is an Eloquent date or datetime cast.
     *
     * @param string $key
     * @return bool
     */
    private function isDateAttribute($key)
    {
        if (in_array($key, $this->getDates(), true)) {
            return true;
        }

        if (!method_exists($this, 'getCasts')) {
            return false;
        }

        $casts = $this->getCasts();
        if (!isset($casts[$key])) {
            return false;
        }

        $cast = strtolower(explode(':', $casts[$key], 2)[0]);

        return in_array($cast, ['date', 'datetime', 'immutable_date', 'immutable_datetime'], true);
    }
}
