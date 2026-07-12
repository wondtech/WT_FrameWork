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

class Wt_AR {
    private array $Lang = [];
    public function getAr(): array {

        ////////////////////////////////////////////////////////// Lang

        $this->Lang['dir']            	    = 'rtl';
        $this->Lang['bootstrap']            = '/pub_wt/css/bootstrap.rtl.min.css';

        ////////////////////////////////////////////////////////// Global Lang

        $this->Lang['brandName']            = 'وندتيك';
        $this->Lang['language']            	= 'اللغة';
        $this->Lang['noData']            	= 'لاتوجد أخبار';
        $this->Lang['firstName']            = 'الاسم الاول';
        $this->Lang['lastname']             = 'الاسم الاخير';
        $this->Lang['fullName']             = 'الإسم بالكامل';
        $this->Lang['email']                = 'البريد الإلكتروني';
        $this->Lang['phoneNumber']          = 'رقم الهاتف';
        $this->Lang['title']                = 'العنوان';
        $this->Lang['message']              = 'الرسالة';
        $this->Lang['captchaCode']          = 'كود التحقق';
        $this->Lang['send']                 = 'إرسال';
        $this->Lang['contact']              = 'الإتصال بـ';
        $this->Lang['contactus']            = 'الإتصال بنا';
        $this->Lang['aboutUs']              = 'من نحن';
        $this->Lang['wondtech']             = 'وندتيك';
        $this->Lang['chat']                 = 'محادثة';
        $this->Lang['password']             = 'كلمة المرور';
        $this->Lang['login']                = 'تسجيل دخول';
        $this->Lang['logout']               = 'تسجيل خروج';
        $this->Lang['sendMessage']          = 'تم إرسال رسالتك بنجاح';
        $this->Lang['checkFields']          = 'يرجى التحقق من الحقول';

        return $this->Lang;
    }
}