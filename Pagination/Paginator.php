<?php

/*
 * Copyright 2015 Ralf Geschke <ralf@kuerbis.org>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Geschke\Bundle\Admin\TranslatorGUIBundle\Pagination;

/**
 * Paginator handling. Maybe move into another bundle in future. 
 */
class Paginator
{

    private $currentPage;
    private $maxPages = null;
    private $maxResults;
    private $pageIdentifier = 'page';
    private $baseUrl;
    private $itemsPerPage;
    public $isPpaginated = false;

    /**
     * Constructor
     * 
     * @param int $currentPage
     * @param int $maxResults
     * @param int $itemsPerPage
     * @param string $pageIdentifier
     */
    public function __construct($currentPage, $maxResults, $itemsPerPage = 10, $pageIdentifier = null)
    {
        $this->currentPage = $currentPage;
        $this->maxResults = $maxResults;
        $this->setItemsPerPage($itemsPerPage);
        if ($pageIdentifier) {
            $this->setPageIdentifier($pageIdentifier);
        }
        $this->calcMaxPages();
    }

    /**
     * Set items shown per page
     * 
     * @param int $count
     */
    public function setItemsPerPage($count = 10)
    {
        $this->itemsPerPage = $count;
        $this->calcMaxPages();
    }

    /**
     * Get items per page setting
     * 
     * @return int
     */
    public function getItemsPerPage()
    {
        return $this->itemsPerPage();
    }

    /**
     * Set base URL, defaults empty, so the current URL will be used
     * 
     * @param string $url
     */
    public function setBaseUrl($url = '')
    {
        $this->baseUrl = $url;
    }

    /**
     * Set page identifier, overwrites the default value 'page'
     * 
     * @param string $identifier
     */
    public function setPageIdentifier($identifier)
    {
        $this->pageIdentifier = $identifier;
    }

    /**
     * Get URL of first page
     * 
     * @return string
     */
    public function getFirst()
    {
        if (strpos($this->baseUrl, '?')) {
            $delimiter = '&';
        } else {
            $delimiter = '?';
        }
        return $this->baseUrl . $delimiter . $this->pageIdentifier . '=1';
    }

    /**
     * Calculate the number of total pages and decide whether pagination is needed
     * 
     * @param bool $force
     * @return int
     */
    protected function calcMaxPages($force = false)
    {
        if (!$this->maxPages or $force) {
            $this->maxPages = ($this->maxResults <= $this->itemsPerPage) ? 1 :
                    intval(floor($this->maxResults / $this->itemsPerPage)) +
                    (($this->maxResults % $this->itemsPerPage) ? 1 : 0);
        }
        if ($this->maxPages > 1) {
            $this->isPaginated = true;
        }
        if ($this->currentPage > $this->maxPages) {
            $this->currentPage = $this->maxPages;
        }

        return $this->maxPages;
    }

    /**
     * Get delimiter dependend to URL position
     * 
     * @return string
     */
    protected function getDelimiter()
    {
        if (strpos($this->baseUrl, '?')) {
            $delimiter = '&';
        } else {
            $delimiter = '?';
        }
        return $delimiter;
    }

    /**
     * Get URL of last page
     * 
     * @return string
     */
    public function getLast()
    {
        $this->calcMaxPages();

        $lastPage = $this->baseUrl . $this->getDelimiter() . $this->pageIdentifier . '=' . $this->maxPages;
        return $lastPage;
    }

    /**
     * Get URL of previus page
     * 
     * @return string
     */
    public function getPrev()
    {
        if ($this->currentPage <= 1) {
            $prevPageNumber = 1;
        } else {
            $prevPageNumber = $this->currentPage - 1;
        }
        $prevPage = $this->baseUrl . $this->getDelimiter() . $this->pageIdentifier . '=' . $prevPageNumber;
        return $prevPage;
    }

    /**
     * Get URL of next page
     * 
     * @return string
     */
    public function getNext()
    {
        $this->calcMaxPages();

        if ($this->currentPage >= $this->maxPages) {
            $nextPageNumber = $this->maxPages;
        } else {
            $nextPageNumber = intval($this->currentPage) + 1;
        }
        $nextPage = $this->baseUrl . $this->getDelimiter() . $this->pageIdentifier . '=' . $nextPageNumber;
        return $nextPage;
    }

    /**
     * Get array of URLs and pages to use with pagination template snippet
     * 
     * @return array
     */
    public function getPages()
    {
        $this->calcMaxPages();
        // simple way: show all pages...
        $pages = array();
        $maxUrls = 10;
        $rangeBottom = $this->currentPage - intval($maxUrls / 2);
        $rangeTop = $this->currentPage + intval($maxUrls / 2);
        if ($rangeBottom <= 1) {
            $rangeBottom = 1;
            $rangeTop = $rangeBottom + ($maxUrls <= $this->maxPages) ? $maxUrls : $this->maxPages;
        }
        if ($rangeTop >= $this->maxPages) {
            $rangeTop = $this->maxPages;
            $rangeBottom = ($rangeTop - $maxUrls <= 1) ? 1 : ($rangeTop - $maxUrls + 1);
        }
        for ($i = $rangeBottom; $i <= $rangeTop; $i++) {
            $pages[] = array('url' => $this->baseUrl . $this->getDelimiter() . $this->pageIdentifier . '=' . $i,
                'number' => $i, 'current' => $i == $this->currentPage);
        }
        return $pages;
    }

    /**
     * Get offset value of current page
     * 
     * @return int
     */
    public function getOffset()
    {
        $offset = intval($this->currentPage - 1) * $this->itemsPerPage;
        if ($offset >= $this->maxPages * $this->itemsPerPage) {
            $offset = ($this->maxPages - 1) * $this->itemsPerPage;
        }
        return $offset;
    }

    /**
     * Get page number of current page
     * 
     * @return string
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * Get maximum number of pages
     * 
     * @return int
     */
    public function getMaxPage()
    {
        return $this->calcMaxPages();
    }

}
