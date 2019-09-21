<?php

namespace Click\Elements\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

/**
 * Triggered when a model is saved.
 */
class ModelSaved
{
    use SerializesModels;

    /**
     * @var Model
     */
    public $model;

    /**
     * Create a new event instance.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }
}
