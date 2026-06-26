<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

/**
 * Config-protected model (no trait): protection comes from
 * demo-mode.write.protected_models / protected_attributes.
 */
class Gadget extends Model
{
    protected $table = 'gadgets';

    protected $guarded = [];

    public $timestamps = false;
}
