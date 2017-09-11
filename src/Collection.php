<?php

namespace FerdinandFrank\LaravelMailChimpNewsletter;

use FerdinandFrank\LaravelMailChimpNewsletter\Models\MailChimpModel;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection as BaseCollection;

class Collection extends BaseCollection {

    /**
     * Find a model in the collection by key.
     *
     * @param  mixed $key
     * @param  mixed $default
     *
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function find($key, $default = null) {
        if ($key instanceof MailChimpModel) {
            $key = $key->getKey();
        }

        if (is_array($key)) {
            if ($this->isEmpty()) {
                return new static;
            }

            return $this->whereIn($this->first()->getKeyName(), $key);
        }

        return Arr::first($this->items, function ($model) use ($key) {
            return $model->getKey() == $key;
        }, $default);
    }

    /**
     * The following methods are intercepted to always return base collections.
     */

    /**
     * Get an array with the values of a given key.
     *
     * @param  string      $value
     * @param  string|null $key
     *
     * @return \Illuminate\Support\Collection
     */
    public function pluck($value, $key = null) {
        return $this->toBase()->pluck($value, $key);
    }

}
