<?php
/**
 * Created by PhpStorm.
 * User: geschke
 * Date: 11.12.2014
 * Time: 13:59
 */


namespace Geschke\Bundle\Admin\TranslatorGUIBundle\Entity;

class MessageTranslation
{
    protected $locale;
    protected $message;
    protected $messageReference;
    protected $translation;

    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message = null)
    {
        $this->message = $message;
    }

    public function getMessageReference()
    {
        return $this->messageReference;
    }

    public function setMessagereference($message = null)
    {
        $this->messageReference = $message;
    }

    public function getTranslation()
    {
        return $this->translation;
    }

    public function setTranslation($translation = null)
    {
        $this->translation = $translation;
    }


}