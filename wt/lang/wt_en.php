<?php
/***********************************************************************
# *          @Project    : WT FrameWork
# *          @version    : 2.0
# *          @author     : Mogbil Sourketti info[@]wondtech.com
# *          @copyright  : 2020 WondTech for Integrated Digital Solutions
# *          @link       : http://www.wondtech.com
# *          @package    : WT FrameWork (2.0)
# ************************************************************************/

namespace WT\LANG;

class Wt_EN {
    private array $Lang = [];
    public function getEn(): array {

        ////////////////////////////////////////////////////////// Lang

        $this->Lang['dir']            	    = 'ltr';
        $this->Lang['bootstrap']            = '/pub_wt/css/bootstrap.min.css';

        ////////////////////////////////////////////////////////// Global Lang

        $this->Lang['brandName']            = 'WondTech';
        $this->Lang['language']            	= 'Language';
        $this->Lang['noData']            	= 'There is no News';
        $this->Lang['firstName']            = 'First Name';
        $this->Lang['lastname']             = 'Last Name';
        $this->Lang['fullName']             = 'Full Name';
        $this->Lang['email']                = 'Email';
        $this->Lang['phoneNumber']          = 'Phone Number';
        $this->Lang['title']                = 'Title';
        $this->Lang['message']              = 'The Message';
        $this->Lang['captchaCode']          = 'Captcha Code';
        $this->Lang['send']                 = 'Send';
        $this->Lang['contact']              = 'Contact';
        $this->Lang['contactus']            = 'Contact Us';
        $this->Lang['aboutUs']              = 'About Us';
        $this->Lang['wondtech']             = 'WondTech';
        $this->Lang['chat']                 = 'Chat';
        $this->Lang['password']             = 'Password';
        $this->Lang['login']                = 'Login';
        $this->Lang['logout']               = 'Logout';
        $this->Lang['sendMessage']          = 'Your Message was Sent Successfully';
        $this->Lang['checkFields']          = 'Please check the fields';

        return $this->Lang;
    }
}