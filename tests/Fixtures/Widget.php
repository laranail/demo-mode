<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Simtabi\Laranail\Demo\Mode\Concerns\PreventsDemoWrites;

/**
 * Trait-protected model: all writes blocked in demo except those in $demoAllowed.
 */
class Widget extends Model
{
    use PreventsDemoWrites;

    protected $table = 'widgets';

    protected $guarded = [];

    public $timestamps = false;

    /**
     * @var list<string>
     */
    public array $demoAllowed = ['create'];
}
