<?php
/**
 * Created by PhpStorm.
 * User: geschke
 * Date: 11.12.2014
 * Time: 13:59
 */


namespace Geschke\Bundle\Admin\TranslatorGUIBundle\Entity;

class MessageTranslationResponse
{
    protected $messageTranslation;
    protected $success;
    protected $bundle;
    protected $locale;

    public function getSuccess()
    {
        return $this->success;
    }

    public function setSuccess($success = null)
    {
        $this->success = $success;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    public function getMessageTranslation()
    {
        return $this->messageTranslation;
    }

    public function setMessageTranslation(MessageTranslation $messageTranslation)
    {
        $this->messageTranslation = $messageTranslation;
    }

    public function getBundle()
    {
        return $this->bundle;
    }

    public function setBundle($bundle = null)
    {
        $this->bundle = $bundle;
    }


}