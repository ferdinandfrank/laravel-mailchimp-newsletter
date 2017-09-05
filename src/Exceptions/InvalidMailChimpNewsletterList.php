<?php

namespace FerdinandFrank\MailChimpNewsletter\Exceptions;

use Exception;

class InvalidMailChimpNewsletterList extends Exception {

    /**
     * @return static
     */
    public static function noListsDefined() {
        return new static('There are no lists defined');
    }

    /**
     * @param string $name
     *
     * @return static
     */
    public static function noListWithName($name) {
        return new static("There is no list named `{$name}`");
    }

    /**
     * @param $defaultListName
     *
     * @return static
     */
    public static function defaultListDoesNotExist($defaultListName) {
        return new static("Could not find a default list named `{$defaultListName}`");
    }
}
