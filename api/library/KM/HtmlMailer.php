<?php

class KM_HtmlMailer extends Zend_Mail
{
    /** @var @_view Zend_View */
    protected $_view;
    protected $viewScriptPath = '/views/scripts/email';
    protected $charset = 'utf8';
    protected $emailOption;
    protected $sendOption = 0;
    protected $template;

    /**
     * @param string $charset
     */
    public function __construct($charset = "utf8") {

        $config = Zend_Registry::get("__CONFIG__");
        $this->emailOption = $config["email"];

        $this->charset = $charset;
        parent::__construct($charset);
        $this->_view = new Zend_View();
        $this->_view->setScriptPath(APPLICATION_PATH . $this->viewScriptPath);

        /** @var $request Zend_Controller_Request_Http */
        $request = Zend_Controller_Front::getInstance()->getRequest();

    }

    public function setViewParam($property, $value) {
        $this->_view->assign($property, $value);
        return $this;
    }

    /**@return Zend_View */
    public function getView() {
        return $this->_view;
    }

    public function setViewScriptPath($viewScriptPath) {
        $this->viewScriptPath = APPLICATION_PATH .$viewScriptPath;
        return $this;
    }

    public function assign($key, $value) {
        $this->_view->assign($key, $value);
        return $this;
    }

    /**
     * @param $template
     * @param string $encoding
     * @return bool
     */
    public function sendHtmlTemplate($template, $encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE)
    {
        $html = $this->_view->render($template);
        $this->setBodyHtml($html, $this->getCharset(), $encoding);

        if (!$this->sendOption) {
            $this->clearRecipients();
            $this->clearSubject();
            return null;
        }

        $result = null;

        try {
            $this->send();
            $this->clearRecipients();
            $this->clearSubject();
            $result = true;
        } catch (Zend_Mail_Exception $e) {
            KM_Log_Me::Log($e->getMessage());
        }
        $log = $this->getDefaultTransport()->getConnection()->getLog();
        return $result;
    }

    /**
     * @param string $subject
     * @param string $domain
     * @param string $template
     * @param array $p
     * @param string $email
     * @return bool|null
     */
    public function sendMail($subject = "", $template = "", $p = array(), $email = "") {

        if ($this->emailOption['send_mail'] == 1) {

            /** $subject */
            if (isNE($subject)) {
                $subject = $this->emailOption['subject'];
            }

            /** $template */
            if (!isNE($template)) {
                $template = APPLICATION_ENV  ."/". $template .".phtml";
            }

            $this->sendOption = 1;
            $this->setSubject($subject);
            $this->addTo($email);
            $this->assign("p" , $p);

            return $this->sendHtmlTemplate($template, Zend_Mime::ENCODING_8BIT) ;

        }
        return null;

    }

    /** @TODO Attachement */
    public function setAttachement(Model_Banner $banner)
    {
        $this->setType(Zend_Mime::MULTIPART_RELATED);

        $bannerAttach = $this->createAttachment(
            file_get_contents( APPLICATION_PATH . "/../public/images/banners/email/".$banner->getInfo() ),
            $banner->mimeByExtension(),
            Zend_Mime::DISPOSITION_INLINE,
            Zend_Mime::ENCODING_BASE64,
            $banner->getInfo()
        );

        $bannerAttach->id = "banner";
        return 1;
    }

    
}
