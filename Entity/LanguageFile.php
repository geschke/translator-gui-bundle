<?php
/**
 * Created by PhpStorm.
 * User: geschke
 * Date: 20.11.2014
 * Time: 22:36
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
        var_dump($this->localeAdditional);
        var_dump($this->locale);
        var_dump($this->bundle);
        var_dump($this->filename);
        die;

        // todo here: check combination of chosen locale and input string of additional field.

        return false;
    }

}