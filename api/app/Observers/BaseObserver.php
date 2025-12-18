<?php

declare(strict_types=1);

namespace App\Observers;

use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

/**
 * Base observer class providing common logging functionality.
 *
 * All observers extending this class will automatically log model events
 * and handle events after commit for transaction safety.
 */
abstract class BaseObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Log a model event with structured data.
     *
     * @param  string  $event  The event name (e.g., 'created', 'updated')
     * @param  mixed  $model  The model instance
     */
    protected function log(string $event, $model): void
    {
        //        logger()->info(sprintf(
        //            '%s event: %s (id: %s)',
        //            class_basename($model),
        //            $event,
        //            $model->id ?? 'n/a',
        //        ), [
        //            'attributes' => $model->getAttributes(),
        //        ]);
    }
}
