<?php

namespace FerdinandFrank\MailChimpNewsletter;

use Illuminate\Support\Collection;
use FerdinandFrank\MailChimpNewsletter\Exceptions\InvalidMailChimpNewsletterList;

class MailChimpNewsletterListCollection extends Collection {

    /** @var string */
    public $defaultListName = '';

    public static function createFromConfig(array $config) {
        $collection = new static();

        foreach ($config['lists'] as $name => $listProperties) {
            $collection->push(new MailChimpNewsletterList($name, $listProperties));
        }

        $collection->defaultListName = $config['defaultListName'];

        return $collection;
    }

    public function findByName(string $name): MailChimpNewsletterList {
        if ((string)$name === '') {
            return $this->getDefault();
        }

        foreach ($this->items as $newsletterList) {
            if ($newsletterList->getName() === $name) {
                return $newsletterList;
            }
        }

        throw InvalidMailChimpNewsletterList::noListWithName($name);
    }

    public function getDefault(): MailChimpNewsletterList {
        foreach ($this->items as $newsletterList) {
            if ($newsletterList->getName() === $this->defaultListName) {
                return $newsletterList;
            }
        }

        throw InvalidMailChimpNewsletterList::defaultListDoesNotExist($this->defaultListName);
    }
}
