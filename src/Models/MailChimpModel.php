<?php

namespace FerdinandFrank\LaravelMailChimpNewsletter\Models;

use ArrayAccess;
use BadMethodCallException;
use DateTime;
use Exception;
use FerdinandFrank\LaravelMailChimpNewsletter\MailChimpHandler;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Concerns\GuardsAttributes;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\Concerns\HasEvents;
use Illuminate\Database\Eloquent\Concerns\HidesAttributes;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Database\Eloquent\MassAssignmentException;
use JsonSerializable;

/**
 * MailChimpModel
 * -----------------------
 * Abstract model class to extend the Eloquent model for the needs of the MailChimp API.
 *
 * @author  Ferdinand Frank
 * @version 1.0
 */
abstract class MailChimpModel implements ArrayAccess, Arrayable, Jsonable, JsonSerializable, UrlRoutable {

    use HasRelationships;
    use HidesAttributes;
    use GuardsAttributes;
    use HasEvents;
    use HasAttributes;

    /**
     * The MailChimp resource name associated with the model.
     *
     * @var string
     */
    protected static $RESOURCE_NAME;

    /**
     * The MailChimp resource name associated with the model that is used by MailChimp on the API responses.
     *
     * @var string
     */
    protected static $RESOURCE_RESPONSE_NAME;

    /**
     * The parent MailChimp model of the model.
     *
     * @var MailChimpModel
     */
    protected $parent;

    /**
     * Indicates if the model exists.
     *
     * @var bool
     */
    public $exists = false;

    /**
     * Indicates if the model was inserted during the current request lifecycle.
     *
     * @var bool
     */
    public $wasRecentlyCreated = false;

    /**
     * The event dispatcher instance.
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected static $dispatcher;

    /**
     * The array of booted models.
     *
     * @var array
     */
    protected static $booted = [];

    /**
     * Creates a new MailChimpModel instance using the specified attributes or the specified id.
     *
     * @param array|string $attributesOrId
     */
    public function __construct($attributesOrId = []) {
        $this->bootIfNotBooted();
        $this->syncOriginal();
        if (is_array($attributesOrId)) {
            $this->fill($attributesOrId);
        } else {
            $this->forceFill(['id' => $attributesOrId]);
        }
    }

    /**
     * Gets the resource name value of the MailChimpModel.
     *
     * @return string
     */
    public static function getResourceName() {
        return static::$RESOURCE_NAME;
    }

    /**
     * Gets the identifying key value of the model.
     *
     * @return mixed
     */
    public function getKey() {
        return $this[$this->getRouteKeyName()];
    }

    /**
     * Sets the identifying key value of the model.
     *
     * @param $key
     */
    public function setKey($key) {
        $this[$this->getRouteKeyName()] = $key;
    }

    /**
     * Gets the name of the identifying key of the model.
     *
     * @return string
     */
    public function getRouteKeyName() {
        return 'id';
    }

    /**
     * Gets the key value of the model that is used in the URLs of the model.
     *
     * @return mixed
     */
    public function getRouteKey() {
        return $this->getKey();
    }

    /**
     * Sets the key value of the model that is used in the URLs of the model.
     *
     * @param $key
     */
    public function setRouteKey($key) {
        $this->setKey($key);
    }

    /**
     * Checks if the model needs to be booted and if so, do it.
     */
    protected function bootIfNotBooted() {
        if (!isset(static::$booted[static::class])) {
            static::$booted[static::class] = true;

            $this->fireModelEvent('booting', false);
            static::boot();
            $this->fireModelEvent('booted', false);
        }
    }

    /**
     * Boots the model.
     */
    protected static function boot() {
        static::bootTraits();
    }

    /**
     * Boots all of the bootable traits on the model.
     */
    protected static function bootTraits() {
        $class = static::class;

        foreach (class_uses_recursive($class) as $trait) {
            if (method_exists($class, $method = 'boot' . class_basename($trait))) {
                forward_static_call([$class, $method]);
            }
        }
    }

    /**
     * Retrieves the model for a bound value.
     *
     * @param  mixed $value
     *
     * @return MailChimpModel
     */
    public function resolveRouteBinding($value) {
        return $this->getHandler()->findModel($value);
    }

    /**
     * Gets the parent model of the model.
     *
     * @return MailChimpModel|null
     */
    public function getParent() {
        return $this->parent;
    }

    /**
     * Sets the parent model for the model.
     *
     * @param MailChimpModel $parent
     */
    public function setParent($parent) {
        $this->parent = $parent;
    }

    /**
     * Gets the name of the resource that shall be used to extract the response data.
     *
     * @return mixed|string
     */
    public static function getResourceResponseName() {
        if (property_exists(get_called_class(), 'RESOURCE_RESPONSE_NAME') && static::$RESOURCE_RESPONSE_NAME) {
            return static::$RESOURCE_RESPONSE_NAME;
        }

        return static::$RESOURCE_NAME;
    }

    /**
     * Gets the path to the model's external general API endpoint on MailChimp.
     *
     * @return string
     */
    public function getIndexApiPath() {
        $path = '';
        $parent = $this->getParent();
        while ($parent != null && $parent instanceof MailChimpModel) {
            $path = $parent::$RESOURCE_NAME . '/' . $parent->getRouteKey() . '/' . $path;
            $parent = $parent->getParent();
        }

        $path .= static::$RESOURCE_NAME;

        return $path;
    }

    /**
     * Gets the path to the model's external detail API endpoint on MailChimp.
     *
     * @return string
     */
    public function getApiPath() {
        $path = $this->getIndexApiPath();
        $key = $this->getRouteKey();

        if ($key) {
            $path .= '/' . $key;
        }

        return $path;
    }

    /**
     * Gets the path to the overview page of this model at MailChimp.
     *
     * @return string
     */
    public static function getRemoteIndexPath() {
        return MailChimpHandler::getUrlToDashboard() . static::$RESOURCE_NAME;
    }

    /**
     * Gets the path to the details page of this model at MailChimp.
     *
     * @return string
     */
    public function getRemotePath() {
        $webId = $this->web_id ?? $this->getRouteKey();

        return MailChimpHandler::getUrlToDashboard() . static::$RESOURCE_NAME . '?id=' . $webId;
    }

    /**
     * Fills the model with the specified array of attributes.
     *
     * @param  array $attributes
     *
     * @return $this
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     */
    public function fill(array $attributes) {
        $totallyGuarded = $this->totallyGuarded();

        foreach ($this->fillableFromArray($attributes) as $key => $value) {

            // The developers may choose to place some attributes in the "fillable" array
            // which means only those attributes may be set through mass assignment to
            // the model, and all others will just get ignored for security reasons.
            if ($this->isFillable($key)) {

                // Set empty string dates (like they are saved on MailChimp) to null
                if (in_array($key, $this->getDates())
                    && $value === '') {
                    $value = null;
                }

                $this->setAttribute($key, $value);
            } elseif ($totallyGuarded) {
                throw new MassAssignmentException($key);
            }
        }

        return $this;
    }

    /**
     * Fills the model with the specified array of attributes. Force mass assignment.
     *
     * @param  array $attributes
     *
     * @return $this
     */
    public function forceFill(array $attributes) {
        return static::unguarded(function () use ($attributes) {
            return $this->fill($attributes);
        });
    }

    /**
     * Updates the model on the MailChimp API.
     *
     * @param  array $attributes
     *
     * @return bool|MailChimpModel
     */
    public function update(array $attributes = []) {
        if (!$this->exists) {
            return false;
        }

        $this->fill($attributes);

        return $this->save();
    }

    /**
     * Saves the model with its attributes using the MailChimp API.
     *
     * @return bool|MailChimpModel
     */
    public function save() {

        // If the "saving" event returns false we'll bail out of the save and return
        // false, indicating that the save failed. This provides a chance for any
        // listeners to cancel save operations if validations fail or whatever.
        if ($this->fireModelEvent('saving') === false) {
            return false;
        }

        // If the model already exists in the database we can just update our record
        // that is already in this database using the current IDs in this "where"
        // clause to only update this model. Otherwise, we'll just insert them.
        if ($this->exists) {
            $saved = $this->isDirty() ?
                $this->performUpdate() : true;
        }

        // If the model is brand new, we'll insert it into our database and set the
        // ID attribute on the model to the value of the newly inserted row's ID
        // which is typically an auto-increment value managed by the database.
        else {
            $saved = $this->performInsert();
        }

        // If the model is successfully saved, we need to do a few more things once
        // that is done. We will call the "saved" method here to run any actions
        // we need to happen after a model gets successfully saved right here.
        if ($saved) {
            $this->finishSave();
        }

        return $saved;
    }

    /**
     * Deletes the model from the MailChimp API.
     *
     * @return array|bool|false
     * @throws Exception
     */
    public function delete() {
        if (is_null($this->getRouteKey())) {
            throw new Exception('No primary key defined on model.');
        }

        // If the model doesn't exist, there is nothing to delete so we'll just return
        // immediately and not do anything else. Otherwise, we will continue with a
        // deletion process on the model, firing the proper events, and so forth.
        if (!$this->exists) {
            return true;
        }

        if ($this->fireModelEvent('deleting') === false) {
            return false;
        }

        // Once the model has been deleted, we will fire off the deleted event so that
        // the developers may hook into post-delete operations. We will then return
        // a boolean true as the delete is presumably successful on the database.
        if ($this->performDelete()) {
            $this->fireModelEvent('deleted', false);

            return true;
        }

        return false;
    }


    /**
     * Performs a model update operation.
     *
     * @return bool
     * @throws Exception
     */
    protected function performUpdate() {

        // If the updating event returns false, we will cancel the update operation so
        // developers can hook Validation systems into their models and cancel this
        // operation if the model does not pass validation. Otherwise, we update.
        if ($this->fireModelEvent('updating') === false) {
            return false;
        }

        // Once we have run the update operation, we will fire the "updated" event for
        // this model instance. This will allow developers to hook into these after
        // models are updated, giving them a chance to do any special processing.
        $dirty = $this->getDirty();

        $response = true;
        if (count($dirty) > 0) {
            $response = $this->getHandler()->updateModel();
            $this->forceFill($response);
            $this->exists = true;

            $this->fireModelEvent('updated', false);

            $this->syncChanges();
        }

        return !!$response;
    }

    /**
     * Performs a model insert operation.
     *
     * @return bool
     * @throws Exception
     */
    protected function performInsert() {
        if ($this->fireModelEvent('creating') === false) {
            return false;
        }

        $response = $this->getHandler()->insertModel();
        $this->forceFill($response);

        // We will go ahead and set the exists property to true, so that it is set when
        // the created event is fired, just in case the developer tries to update it
        // during the event. This will allow them to do so and run an update here.
        $this->exists = true;

        $this->wasRecentlyCreated = true;

        $this->fireModelEvent('created', false);

        return !!$response;
    }

    /**
     * Performs the actual delete query on this model instance.
     */
    protected function performDelete() {
        $result = $this->getHandler()->deleteModel();
        $this->exists = false;

        return !!$result;
    }

    /**
     * Performs any actions that are necessary after the model is saved.
     */
    protected function finishSave() {
        $this->fireModelEvent('saved', false);
        $this->syncOriginal();
    }

    /**
     * Gets an attribute from the model.
     *
     * @param  string $key
     *
     * @return mixed
     */
    public function getAttribute($key) {
        if (!$key) {
            return null;
        }

        // Check if a relationship exists for that key
        $relationValue = $this->getRelationValue($key);
        if ($relationValue) {
            return $relationValue;
        }

        // If the attribute exists in the attribute array or has a "get" mutator we will
        // get the attribute's value.
        $value = null;
        if (array_key_exists($key, $this->attributes)
            || $this->hasGetMutator($key)) {
            $value = $this->getAttributeValue($key);
        }
        if (empty($value)) {
            $value = null;
        }

        return $value;
    }

    /**
     * Gets a relationship value from a method.
     *
     * @param  string $method
     *
     * @return mixed
     * @throws \LogicException
     */
    protected function getRelationshipFromMethod($method) {
        $relation = $this->$method();
        $this->setRelation($method, $relation);

        return $relation;
    }

    /**
     * Gets the attributes that should be converted to dates.
     *
     * @return array
     */
    public function getDates() {
        return $this->dates;
    }

    /**
     * Gets the format for dates stored on MailChimp.
     *
     * @return string
     */
    protected function getDateFormat() {
        return DateTime::ATOM;
    }

    /**
     * Gets the casts array.
     *
     * @return array
     */
    public function getCasts() {
        return $this->casts;
    }

    /**
     * Adds the date attributes to the attributes array.
     *
     * @param  array $attributes
     *
     * @return array
     */
    protected function addDateAttributesToArray(array $attributes) {
        foreach ($this->getDates() as $key) {

            // Set null dates to an empty string, as specified by the MailChimp API.
            if (!isset($attributes[$key])) {
                $attributes[$key] = '';
            } else {
                $attributes[$key] = $this->serializeDate(
                    $this->asDateTime($attributes[$key])
                );
            }
        }

        return $attributes;
    }

    /**
     * Dynamically retrieves attributes on the model.
     *
     * @param  string $key
     *
     * @return mixed
     */
    public function __get($key) {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically sets attributes on the model.
     *
     * @param  string $key
     * @param  mixed  $value
     */
    public function __set($key, $value) {
        $this->setAttribute($key, $value);
    }

    /**
     * Converts the model instance to an array.
     *
     * @return array
     */
    public function toArray() {
        return $this->attributesToArray();
    }

    /**
     * Converts the model instance to JSON.
     *
     * @param  int $options
     *
     * @return string
     * @throws \Illuminate\Database\Eloquent\JsonEncodingException
     */
    public function toJson($options = 0) {
        $json = json_encode($this->jsonSerialize(), $options);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw JsonEncodingException::forModel($this, json_last_error_msg());
        }

        return $json;
    }

    /**
     * Converts the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize() {
        return $this->toArray();
    }

    /**
     * Converts the model to its string representation.
     *
     * @return string
     */
    public function __toString() {
        return $this->toJson();
    }

    /**
     * Determines if the given attribute exists.
     *
     * @param  mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset) {
        return !is_null($this->getAttribute($offset));
    }

    /**
     * Gets the value for a given offset.
     *
     * @param  mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset) {
        return $this->getAttribute($offset);
    }

    /**
     * Sets the value for a given offset.
     *
     * @param  mixed $offset
     * @param  mixed $value
     */
    public function offsetSet($offset, $value) {
        $this->setAttribute($offset, $value);
    }

    /**
     * Unsets the value for a given offset.
     *
     * @param  mixed $offset
     */
    public function offsetUnset($offset) {
        unset($this->attributes[$offset]);
    }

    /**
     * Determines if an attribute or relation exists on the model.
     *
     * @param  string $key
     *
     * @return bool
     */
    public function __isset($key) {
        return $this->offsetExists($key);
    }

    /**
     * Unsets an attribute on the model.
     *
     * @param  string $key
     */
    public function __unset($key) {
        $this->offsetUnset($key);
    }

    /**
     * Handles dynamic method calls into the model.
     *
     * @param  string $method
     * @param  array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters) {
        try {
            return $this->getHandler()->$method(...$parameters);
        } catch (BadMethodCallException $e) {
            throw new BadMethodCallException(
                sprintf('Call to undefined method %s::%s()', get_class($this), $method)
            );
        }
    }

    /**
     * Handles dynamic static method calls into the method.
     *
     * @param  string $method
     * @param  array  $parameters
     *
     * @return mixed
     */
    public static function __callStatic($method, $parameters) {
        return (new static)->$method(...$parameters);
    }

    /**
     * Gets a new query builder for the model's API endpoint.
     *
     * @return MailChimpHandler
     */
    public function getHandler() {
        return new MailChimpHandler($this);
    }

}