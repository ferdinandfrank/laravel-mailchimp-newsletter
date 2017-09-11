<?php

namespace FerdinandFrank\LaravelMailChimpNewsletter\Models;

/**
 * HasRelationships
 * -----------------------
 * Trait to define properties and functions for MailChimpModels that define relationships.
 * -----------------------
 *
 * @author  Ferdinand Frank
 * @version 1.0
 * @package FerdinandFrank\LaravelMailChimpNewsletter\Models
 */

trait HasRelationships {

    /**
     * The loaded relationships for the model.
     *
     * @var array
     */
    protected $relations = [];

    /**
     * Gets all the loaded relations for the instance.
     *
     * @return array
     */
    public function getRelations() {
        return $this->relations;
    }

    /**
     * Gets a specified relationship.
     *
     * @param  string $relation
     *
     * @return mixed
     */
    public function getRelation($relation) {
        return $this->relations[$relation];
    }

    /**
     * Determines if the given relation is loaded.
     *
     * @param  string $key
     *
     * @return bool
     */
    public function relationLoaded($key) {
        return array_key_exists($key, $this->relations);
    }

    /**
     * Sets the specific relationship in the model.
     *
     * @param  string $relation
     * @param  mixed  $value
     *
     * @return $this
     */
    public function setRelation($relation, $value) {
        $this->relations[$relation] = $value;

        return $this;
    }

    /**
     * Sets the entire relations array on the model.
     *
     * @param  array $relations
     *
     * @return $this
     */
    public function setRelations(array $relations) {
        $this->relations = $relations;

        return $this;
    }
}