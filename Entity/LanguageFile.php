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

class LanguageFile
{
    protected $locale;
    protected $filename;
    protected $bundle;
    protected $localeAdditional;

    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function setFilename($filename = null)
    {
        $this->filename = $filename;
    }

    public function getBundle()
    {
        return $this->bundle;
    }

    public function setBundle($bundle = null)
    {
        $this->bundle = $bundle;
    }

    public function getLocaleAdditional()
    {
        return $this->localeAdditional;
    }

    public function setLocaleAdditional($locale)
    {
        $this->localeAdditional = $locale;
    }

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