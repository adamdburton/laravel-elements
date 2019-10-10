<?php

namespace Click\Elements\Concerns\Element;

use Click\Elements\Builder;
use Illuminate\Support\Str;

/**
 * Trait HasScopes
 */
trait HasScopes
{

    /**
     * @param $key
     * @return bool
     */
    public function hasScope($key)
    {
        return method_exists($this, 'scope' . Str::studly($key));
    }

    /**
     * @param $key
     * @param Builder $query
     * @param array $arguments
     * @return mixed
     */
    public function applyScope($key, Builder $query, $arguments = [])
    {
        array_unshift($arguments, $query);

        return $this->{'scope' . Str::studly($key)}(...$arguments);
    }
}
