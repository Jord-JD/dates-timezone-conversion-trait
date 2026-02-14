# ⏳🗺 Dates Timezone Conversion Trait

This package provides a trait that automatically converts an Eloquent
model's dates to and from the current user's timezone.

## Installation

Dates Timezone Conversion Trait can be easily installed using Composer.
Just run the following command from the root of your project.

```
composer require jord-jd/dates-timezone-conversion-trait
```

If you have never used the Composer dependency manager before, head
to the [Composer website](https://getcomposer.org/) for more
information on how to get started.

## Usage

First, you must add a `timezone` field to your application's main `User`
model and populate it with an appropriate timezone. Please see this [list
 of supported timezones](https://secure.php.net/manual/en/timezones.php).

Then, to benefit from this trait, simply `use` it within any Eloquent Model.
An example of a `User` model with the trait being used is shown below.

```php
<?php

namespace App\Models;

use JordJD\DatesTimezoneConversion\Traits\DatesTimezoneConversion;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable, DatesTimezoneConversion;

    protected $dates = [
        'last_logged_in_at',
        'created_at',
        'updated_at'
    ];

    /* snipped */
}
```

After using the trait, the following transformation will automatically be
applied to any attributes defined in the model's `$dates` array, if a
user is currently logged in.

* When reading an attribute (e.g. `$user->last_logged_in_at`), the datetime
object will automatically be converted to the user's timezone.

* When writing to an attribute (e.g. `$user->last_logged_in_at`), the datetime
will automatically be converted to the Laravel application's timezone (as
defined in the `config/app.php` file).
