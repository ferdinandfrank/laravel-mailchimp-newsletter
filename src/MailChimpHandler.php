<?php

namespace FerdinandFrank\LaravelMailChimpNewsletter;

use Exception;
use FerdinandFrank\LaravelMailChimpNewsletter\Models\MailChimpModel;
use FerdinandFrank\LaravelMailChimpNewsletter\Models\NewsletterList;

/**
 * MailChimpHandler
 * -----------------------
 * The main class to operate on the MailChimp API for a specific model.
 *
 * @author  Ferdinand Frank
 * @version 1.0
 */
class MailChimpHandler {

    /**
     * The model being queried.
     *
     * @var MailChimpModel
     */
    protected $model;


    /**
     * Creates a new Builder instance.
     *
     * @param MailChimpModel $model
     */
    public function __construct(MailChimpModel $model) {
        $this->model = $model;
    }

    /**
     * Gets all of the models from the MailChimp API.
     *
     * @param  array $attributes The attributes to receive
     * @param int    $count
     *
     * @return Collection
     */
    public function all($count = 10, $attributes = []) {
        $path = $this->model->getApiPath();
        $response = $this->get($path, ['fields' => implode(",", $attributes), 'count' => $count]);
        $models = new Collection();
        foreach ($response[$this->model::getResourceResponseName()] as $modelAttributes) {
            $model = (new $this->model())->forceFill($modelAttributes);
            $model->exists = true;
            $models->push($model);
        }

        return $models;
    }

    /**
     * Sets the parent for the model of this builder to get the results for.
     *
     * @param MailChimpModel $parent
     *
     * @return $this
     */
    public function forParent($parent) {
        $this->model->setParent($parent);

        return $this;
    }

    /**
     * Finds a model by its primary key on the MailChimp API.
     *
     * @param  mixed              $key         The primary key of the model to receive
     * @param  array              $attributes The attributes to receive
     *
     * @return MailChimpModel
     */
    public function findModel($key, $attributes = []) {
        $this->model->setRouteKey($key);
        $path = $this->model->getApiPath();
        try {
            $response = $this::get($path, ['fields' => implode(",", $attributes)]);
        } catch (Exception $exception) {
            // Model not found
            return null;
        }

        // Model exists so create the model to return
        $model = (new $this->model())->forceFill($response);
        $model->exists = true;

        return $model;
    }

    /**
     * Gets the model from the MailChimp API. It is expected that only one model exists on the index endpoint of the model.
     * If multiple models exist, the 'all' function should be used.
     *
     * @param  array              $attributes The attributes to receive
     *
     * @return MailChimpModel
     */
    public function getModel($attributes = []) {
        $path = $this->model->getIndexApiPath();
        $response = $this::get($path, ['fields' => implode(",", $attributes)]);

        // Model exists so create the model to return
        $model = (new $this->model())->forceFill($response);
        $model->exists = true;

        return $model;
    }

    /**
     * Makes a search request on the MailChimp API to search for corresponding models that matches the specified query.
     * Optionally only searches on the specified list.
     *
     * @param string $query
     * @param string|null   $listId
     *
     * @return Collection
     */
    public function searchModel(string $query, $listId = null) {
        if ($listId instanceof NewsletterList) {
            $listId = $listId->getRouteKey();
        }

        $args = ['query' => $query];
        if ($listId) {
            $args = array_merge($args, ['list_id' => $listId]);
        }

        $response = $this->get("search-{$this->model::getResourceName()}", $args);
        $models = new Collection();
        foreach ($response['results'] as $modelAttributes) {
            $model = (new $this->model())->forceFill($modelAttributes);
            $model->exists = true;
            $models->push($model);
        }

        return $models;
    }

    /**
     * Checks if the model with the specified key exists on the MailChimp API.
     *
     * @param $key
     *
     * @return bool
     */
    public function modelExists($key) {
        $model = $this->findModel($key);

        return $model != null;
    }

    /**
     * Updates the model on the MailChimp API with the data of the managed model.
     *
     * @return array
     * @throws Exception
     */
    public function updateModel() {
        $path = $this->model->getApiPath();
        $response = $this->patch($path, $this->model->toArray());

        return $this->handleResponse($response);
    }

    /**
     * Inserts a new model on the MailChimp API with the data of the managed model.
     *
     * @return array
     * @throws Exception
     */
    public function insertModel() {
        $path = $this->model->getIndexApiPath();
        $response = $this->post($path, $this->model->toArray());

        return $this->handleResponse($response);
    }

    /**
     * Deletes the model with the key of the managed model on the MailChimp API.
     *
     * @return array
     * @throws Exception
     */
    public function deleteModel() {
        $path = $this->model->getApiPath();
        $response = $this->delete($path);

        return $this->handleResponse($response);
    }

    /**
     * Makes a basic GET request to the specified path on the MailChimp API with the specified attributes.
     *
     * @param $path
     * @param $attributes
     *
     * @return array
     */
    public static function get($path, $attributes = []) {
        $response = \MailChimp::get($path, $attributes);

        return static::handleResponse($response);
    }

    /**
     * Makes a basic POST request to the specified path on the MailChimp API with the specified attributes.
     *
     * @param $path
     * @param $attributes
     *
     * @return array
     */
    public static function post($path, $attributes = []) {
        $response = \MailChimp::post($path, $attributes);

        return static::handleResponse($response);
    }

    /**
     * Makes a basic PATCH request to the specified path on the MailChimp API with the specified attributes.
     *
     * @param $path
     * @param $attributes
     *
     * @return array
     */
    public static function patch($path, $attributes = []) {
        $response = \MailChimp::patch($path, $attributes);

        return static::handleResponse($response);
    }

    /**
     * Makes a basic DELETE request to the specified path on the MailChimp API with the specified attributes.
     *
     * @param $path
     * @param $attributes
     *
     * @return array
     */
    public static function delete($path, $attributes = []) {
        $response = \MailChimp::delete($path, $attributes);

        return static::handleResponse($response);
    }

    /**
     * Makes a basic PUT request to the specified path on the MailChimp API with the specified attributes.
     *
     * @param $path
     * @param $attributes
     *
     * @return array
     */
    public static function put($path, $attributes = []) {
        $response = \MailChimp::put($path, $attributes);

        return static::handleResponse($response);
    }

    /**
     * Handles possible errors on a response from the MailChimp API.
     *
     * @param $response
     *
     * @return array
     * @throws Exception
     */
    private static function handleResponse($response) {
        if (!static::lastActionSucceeded()) {
            throw new Exception(\MailChimp::getLastError() . '\n Last Response: ' . static::getLastResponse() . '\n Last Request: ' . static::getLastRequest());
        }

        return $response;
    }

    /**
     * Gets the URL to the dashboard at MailChimp.
     *
     * @return string
     */
    public static function getUrlToDashboard() {
        $apiEndpoint = \MailChimp::getApiEndpoint();
        $endpoint = explode(".api", $apiEndpoint)[0];

        return "{$endpoint}.admin.mailchimp.com/";
    }

    /**
     * Gets the body content of the last response.
     *
     * @return string
     */
    public static function getLastResponse() {
        $response = \MailChimp::getLastResponse();
        if ($response) {
            return $response['body'];
        }
        return null;
    }

    /**
     * Gets the body content of the last request.
     *
     * @return string
     */
    public static function getLastRequest() {
        $response = \MailChimp::getLastRequest();
        if ($response) {
            return $response['body'];
        }
        return null;
    }

    /**
     * Gets the errors of the last response.
     *
     * @return string
     */
    public static function getLastError() {
        return \MailChimp::getLastError();
    }

    /**
     * Checks if the last API call was successful.
     *
     * @return bool
     */
    public static function lastActionSucceeded() {
        return \MailChimp::success();
    }

    /**
     * Turns the specified email address into a MailChimp newsletter list member hash.
     *
     * @param string $email
     *
     * @return string
     */
    public static function getSubscriberHash($email) {
        if (!$email) {
            return $email;
        }

        return \MailChimp::subscriberHash($email);
    }
}
