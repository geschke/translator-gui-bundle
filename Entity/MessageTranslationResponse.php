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
    protected $messageTranslationReference;
    protected $success;
    protected $bundle;
    protected $locale;
    protected $domain;

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

    public function getDomain()
    {
        return $this->domain;
    }

    public function setDomain($domain)
    {
        $this->domain = $domain;
    }


    public function getMessageTranslation()
    {
        return $this->messageTranslation;
    }

    public function setMessageTranslation(MessageTranslation $messageTranslation)
    {
        $this->messageTranslation = $messageTranslation;
    }

    public function getMessageTranslationReference()
    {
        return $this->messageTranslationReference;
    }

    public function setMessageTranslationReference(MessageTranslation $messageTranslation)
    {
        $this->messageTranslationReference = $messageTranslation;
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