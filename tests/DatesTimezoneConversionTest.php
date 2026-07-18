<?php

namespace JordJD\DatesTimezoneConversion\Tests;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use JordJD\DatesTimezoneConversion\Traits\DatesTimezoneConversion;
use PHPUnit\Framework\TestCase;

class DatesTimezoneConversionTest extends TestCase
{
    public function testNullableDatePassesThroughUnchanged()
    {
        $this->boot(null);
        $model = new TestModel();

        $model->setAttribute('event_at', null);

        $this->assertNull($model->getAttributes()['event_at']);
    }

    public function testDateCastUsesUserTimezoneOnInput()
    {
        $this->boot(new TestUser('Europe/London'));
        $model = new TestModel();

        $model->setAttribute('event_at', '2026-07-01 12:00:00');
        $this->assertSame('2026-07-01 11:00:00', $model->getAttributes()['event_at']);
    }

    private function boot($user)
    {
        $container = new Container();
        $container->instance('config', new TestConfig());
        $container->instance('auth', new TestAuth($user));
        Container::setInstance($container);
        Auth::setFacadeApplication($container);
    }
}

class TestModel extends Model
{
    use DatesTimezoneConversion;

    public $timestamps = false;
    protected $dates = ['event_at'];
    protected $casts = ['event_at' => 'datetime'];
}

class TestUser extends Model
{
    public function __construct($timezone)
    {
        parent::__construct();
        $this->setRawAttributes(['timezone' => $timezone]);
    }
}

class TestAuth
{
    private $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function user()
    {
        return $this->user;
    }
}

class TestConfig
{
    public function get($key, $default = null)
    {
        return $key === 'app.timezone' ? 'UTC' : $default;
    }
}
