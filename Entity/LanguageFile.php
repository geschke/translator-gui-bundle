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

namespace Geschke\Bundle\Admin\TranslatorGUIBundle\Entity;

/**
 * LanguageFile entity
 */
class LanguageFile
{
    protected $locale;
    protected $filename;
    protected $bundle;
    protected $localeAdditional;

    /**
     * Get locale value
     * 
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set locale value
     * 
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Get filename value
     * 
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set filename value
     * 
     * @param string $filename, default null
     */
    public function setFilename($filename = null)
    {
        $this->filename = $filename;
    }

    /**
     * Get bundle name
     * 
     * @return string
     */
    public function getBundle()
    {
        return $this->bundle;
    }

    /**
     * Set bundle name
     * 
     * @param string $bundle
     */
    public function setBundle($bundle = null)
    {
        $this->bundle = $bundle;
    }

    /**
     * Get user-defined locale string
     * 
     * @return string
     */
    public function getLocaleAdditional()
    {
        return $this->localeAdditional;
    }

    /**
     * Set user-defined locale string
     * 
     * @param string $locale
     */
    public function setLocaleAdditional($locale)
    {
        $this->localeAdditional = $locale;
    }

    /**
     * Validation method, returns true if locale is chosen from 
     * radio button field or with filled additional, i.e. user-defined text field
     * 
     * @return bool
     */
    public function isLanguageChosen()
    {
        if (!$this->locale) {
            return false;
        }
        if ($this->locale == 'misc') {
            if (!trim($this->localeAdditional)) {
                return false;
            }
            else {
                $this->locale = trim($this->localeAdditional);
                return true;
            }
        }
        return true;

    }

}