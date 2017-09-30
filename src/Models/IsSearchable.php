<?php

namespace FerdinandFrank\LaravelMailChimpNewsletter\Models;

use FerdinandFrank\LaravelMailChimpNewsletter\Collection;
use Illuminate\Pagination\Paginator;

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
     * @param string      $query
     * @param string|null $listId
     * @param array       $attributes
     * @param int         $offset
     *
     * @return Collection
     */
    public static function searchModel(string $query, $listId = null, $attributes = [], $offset = 0) {

        if ($listId instanceof NewsletterList) {
            $listId = $listId->getRouteKey();
        }

        $args = ['query' => $query];
        if ($listId) {
            $args = array_merge($args, ['list_id' => $listId, 'fields' => implode(",", $attributes), 'offset' => $offset]);
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
     * Paginate the given query.
     *
     * @param string    $query
     * @param null      $listId
     * @param  int      $perPage
     * @param  array    $attributes
     * @param  string   $pageName
     * @param  int|null $page
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginateWithSearch(string $query, $listId = null, $perPage = null, $attributes = [], $pageName = 'page', $page = null) {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);
        $perPage = $perPage ?: $this->getPerPage();

        $offset = ($page - 1) * $perPage;

        $results = $this->searchModel($query, $listId, $attributes, $offset)->take($perPage);
        $total = count($results) + $offset;

        return $this->paginator($results, $total, $perPage, $page, [
            'path'     => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
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