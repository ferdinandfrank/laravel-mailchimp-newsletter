<?php

namespace FerdinandFrank\LaravelMailChimpNewsletter\Models;

use FerdinandFrank\LaravelMailChimpNewsletter\Collection;

/**
 * IsSearchable
 * -----------------------
 * Represents the functionality of MailChimpModels that can be searched.
 * -----------------------
 *
 * @author  Ferdinand Frank
 * @version 1.0
 * @package FerdinandFrank\LaravelMailChimpNewsletter\Models
 */

trait IsSearchable {

    /**
     * Makes a search request on the MailChimp API to search for corresponding models that matches the specified query.
     * Optionally only searches on the specified list.
     *
     * @param string $query
     * @param string|null   $listId
     *
     * @return Collection
     */
    public static function searchModel(string $query, $listId = null) {

        if ($listId instanceof NewsletterList) {
            $listId = $listId->getRouteKey();
        }

        $args = ['query' => $query];
        if ($listId) {
            $args = array_merge($args, ['list_id' => $listId]);
        }

        $model = new static();
        $response = $model->get("search-{$model->getResourceName()}", $args);
        $result = static::getSearchResultsFromResponse($response);

        $models = new Collection();
        foreach ($result as $modelAttributes) {
            $modelAttributes = static::getModelFromSearchResults($modelAttributes);
            $model = (new static())->forceFill($modelAttributes);
            $model->exists = true;

            // Searching will also find models which got deleted within the last 30 days. But these models cannot
            // be found on the API. So we don't want to include those as they cause errors.
            if ($model->findModel($model->getKey())) {
                $models->push($model);
            }
        }

        return $models;
    }

    /**
     * Gets the array of the specified array that contains the results of a search query.
     *
     * @param array $response
     *
     * @return array
     */
    protected static function getSearchResultsFromResponse(array $response) {
        return $response['results'];
    }

    /**
     * Gets the array from the search result that contains the model attributes.
     *
     * @param array $result
     *
     * @return array
     */
    protected static function getModelFromSearchResults(array $result) {
        return $result;
    }
}