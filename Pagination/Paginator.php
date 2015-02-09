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

class Paginator
{
    private $currentPage;

    private $maxPages = null;

    private $maxResults;

    private $pageIdentifier = 'page';

    private $baseUrl;

    private $itemsPerPage;

    public $is_paginated = false;

    public function __construct($currentPage, $maxResults, $itemsPerPage = 10, $pageIdentifier = null)
    {
        $this->currentPage = $currentPage;
        $this->maxResults = $maxResults;
        $this->setItemsPerPage($itemsPerPage);
        if ($pageIdentifier) $this->setPageIdentifier($pageIdentifier);
        $this->calcMaxPages();
    }

    public function setItemsPerPage($count = 10)
    {
        $this->itemsPerPage = $count;
        $this->calcMaxPages();
    }

    public function getItemsPerPage()
    {
        return $this->itemsPerPage();
    }

    public function setBaseUrl($url = '')
    {
        $this->baseUrl = $url;
    }

    public function setPageIdentifier($identifier)
    {
        $this->pageIdentifier = $identifier;
    }

    public function getFirst()
    {
        if (strpos($this->baseUrl, '?'))
        {
            $delimiter = '&';
        } else
        {
            $delimiter = '?';
        }
        return $this->baseUrl . $delimiter . $this->pageIdentifier . '=1';
    }

    protected function calcMaxPages($force = false)
    {
        if (!$this->maxPages or $force)
        {
            $this->maxPages = ($this->maxResults <= $this->itemsPerPage) ? 1 :
                intval(floor($this->maxResults / $this->itemsPerPage)) +
                (($this->maxResults % $this->itemsPerPage) ? 1 : 0) ;
        }
        if ($this->maxPages > 1)
        {
            $this->is_paginated = true;
        }
        if ($this->currentPage > $this->maxPages) {
            $this->currentPage = $this->maxPages;
        }

        return $this->maxPages;
    }

    protected function getDelimiter()
    {
        if (strpos($this->baseUrl, '?'))
        {
            $delimiter = '&';
        } else
        {
            $delimiter = '?';
        }
        return $delimiter;
    }

    public function getLast()
    {
        $this->calcMaxPages();

        $lastPage = $this->baseUrl . $this->getDelimiter() . $this->pageIdentifier . '=' . $this->maxPages;
        return $lastPage;
    }

    public function getPrev()
    {
        if ($this->currentPage <= 1)
            $prevPageNumber = 1;
        else
            $prevPageNumber = $this->currentPage - 1;
        $prevPage = $this->baseUrl . $this->getDelimiter() . $this->pageIdentifier . '=' . $prevPageNumber;
        return $prevPage;
    }

    public function getNext()
    {
        $this->calcMaxPages();

        if ($this->currentPage >= $this->maxPages)
            $nextPageNumber = $this->maxPages;
        else
            $nextPageNumber = intval($this->currentPage) + 1;
        $nextPage = $this->baseUrl . $this->getDelimiter() . $this->pageIdentifier . '=' . $nextPageNumber;
        return $nextPage;
    }

    public function getPages()
    {
        $this->calcMaxPages();
        // simple way: show all pages...
        $pages = array();
        $maxUrls = 10;
        $rangeBottom = $this->currentPage - intval($maxUrls / 2);
        $rangeTop = $this->currentPage + intval($maxUrls / 2);
        if ($rangeBottom <= 1)
        {
            $rangeBottom = 1;
            $rangeTop = $rangeBottom + ($maxUrls <= $this->maxPages) ? $maxUrls : $this->maxPages;
        }
        if ($rangeTop >= $this->maxPages)
        {
            $rangeTop = $this->maxPages;
            $rangeBottom = ($rangeTop - $maxUrls <= 1) ? 1 : ($rangeTop - $maxUrls + 1);
        }
        for ($i = $rangeBottom; $i <= $rangeTop; $i++)
        {
            $pages[] = array('url' => $this->baseUrl . $this->getDelimiter() . $this->pageIdentifier . '=' . $i,
                'number' => $i, 'current' => $i == $this->currentPage);
        }
        return $pages;
    }

    public function getOffset()
    {
        $offset = intval($this->currentPage - 1) * $this->itemsPerPage;
        if ($offset >= $this->maxPages * $this->itemsPerPage)
        {
            $offset = ($this->maxPages - 1) * $this->itemsPerPage;
        }
        return $offset;
    }

    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    public function getMaxPage()
    {
        return $this->calcMaxPages();
    }
}