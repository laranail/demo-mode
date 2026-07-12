<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Concerns;

/**
 * Opt-in marker trait: a model using it has create/update/delete/restore blocked
 * while demo mode is active. List operations that should REMAIN permitted in a
 * public `$demoAllowed` array (default: none — all blocked):
 *
 *   class Comment extends Model {
 *       use PreventsDemoWrites;
 *       public array $demoAllowed = ['create']; // visitors may comment, not edit/delete
 *   }
 *
 * Enforcement is performed by the package's global Eloquent write listener via
 * {@see EloquentWriteGuard}, so this trait
 * needs no boot logic. (Mass update()/delete() bypass model events — enable the
 * strict connection guard to cover those.)
 */
trait PreventsDemoWrites
{
    //
}
