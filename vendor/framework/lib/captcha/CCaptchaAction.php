<?php
namespace framework\lib\captcha;

use framework\App;
use framework\bin\ABaseApplication;

/**
 * CCaptcha class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CCaptcha renders a CAPTCHA image element.
 *
 * CCaptcha is used together with {@link CCaptchaAction} to provide {@link http://en.wikipedia.org/wiki/Captcha CAPTCHA}
 * - a way of preventing site spam.
 *
 * The image element rendered by CCaptcha will display a CAPTCHA image generated
 * by an action of class {@link CCaptchaAction} belonging to the current controller.
 * By default, the action ID should be 'captcha', which can be changed by setting {@link captchaAction}.
 *
 * CCaptcha may also render a button next to the CAPTCHA image. Clicking on the button
 * will change the CAPTCHA image to be a new one in an AJAX way.
 *
 * If {@link clickableImage} is set true, clicking on the CAPTCHA image
 * will refresh the CAPTCHA.
 *
 * A {@link CCaptchaValidator} may be used to validate that the user enters
 * a verification code matching the code displayed in the CAPTCHA image.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.widgets.captcha
 * @since 1.0
 */
class CCaptcha
{

    /**
     * @var string the ID of the action that should provide CAPTCHA image. Defaults to 'captcha',
     * meaning the 'captcha' action of the current controller. This property may also
     * be in the format of 'ControllerID/ActionID'. Underneath, this property is used
     * by {@link CController::createUrl} to create the URL that would serve the CAPTCHA image.
     * The action has to be of {@link CCaptchaAction}.
     */
    public $captchaAction = 'captcha';

    /**
     * @var boolean whether to display a button next to the CAPTCHA image. Clicking on the button
     * will cause the CAPTCHA image to be changed to a new one. Defaults to true.
     */
    public $showRefreshButton = true;

    /**
     * @var boolean whether to allow clicking on the CAPTCHA image to refresh the CAPTCHA letters.
     * Defaults to false. Hint: you may want to set {@link showRefreshButton} to false if you set
     * this property to be true because they serve for the same purpose.
     * To enhance accessibility, you may set {@link imageOptions} to provide hints to end-users that
     * the image is clickable.
     */
    public $clickableImage = false;

    /**
     * @var string the label for the refresh button. Defaults to 'Get a new code'.
     */
    public $buttonLabel;

    /**
     * @var string the type of the refresh button. This should be either 'link' or 'button'.
     * The former refers to hyperlink button while the latter a normal push button.
     * Defaults to 'link'.
     */
    public $buttonType = 'link';

    /**
     * @var array HTML attributes to be applied to the rendered image element.
     */
    public $imageOptions = array();

    /**
     * @var array HTML attributes to be applied to the rendered refresh button element.
     */
    public $buttonOptions = array();

    /**
     * Renders the widget.
     */
    public function run()
    {
        if (self::checkRequirements('imagick') || self::checkRequirements('gd')) {
            $this->renderImage();
            $this->registerClientScript();
        } else
            throw new CException(Yii::t('yii', 'GD with FreeType or ImageMagick PHP extensions are required.'));
    }

    /**
     * Renders the CAPTCHA image.
     */
    protected function renderImage()
    {
        if (!isset($this->imageOptions['id']))
            $this->imageOptions['id'] = $this->getId();

        $url = $this->getController()->createUrl($this->captchaAction, array('v' => uniqid()));
        $alt = isset($this->imageOptions['alt']) ? $this->imageOptions['alt'] : '';
        echo CHtml::image($url, $alt, $this->imageOptions);
    }

    /**
     * Registers the needed client scripts.
     */
    public function registerClientScript()
    {
        $cs = Yii::app()->clientScript;
        $id = $this->imageOptions['id'];
        $url = $this->getController()->createUrl($this->captchaAction, array(CCaptchaAction::REFRESH_GET_VAR => true));

        $js = "";
        if ($this->showRefreshButton) {
            // reserve a place in the registered script so that any enclosing button js code appears after the captcha js
            $cs->registerScript('Yii.CCaptcha#' . $id, '// dummy');
            $label = $this->buttonLabel === null ? Yii::t('yii', 'Get a new code') : $this->buttonLabel;
            $options = $this->buttonOptions;
            if (isset($options['id'])) $buttonID = $options['id'];
            else $buttonID = $options['id'] = $id . '_button';
            if ($this->buttonType === 'button')
                $html = CHtml::button($label, $options);
            else $html = CHtml::link($label, $url, $options);
            $js = "jQuery('#$id').after(" . CJSON::encode($html) . ");";
            $selector = "#$buttonID";
        }

        if ($this->clickableImage)
            $selector = isset($selector) ? "$selector, #$id" : "#$id";

        if (!isset($selector)) return;

        $js .= "
jQuery(document).on('click', '$selector', function(){
	jQuery.ajax({
		url: " . CJSON::encode($url) . ",
		dataType: 'json',
		cache: false,
		success: function(data) {
			jQuery('#$id').attr('src', data['url']);
			jQuery('body').data('{$this->captchaAction}.hash', [data['hash1'], data['hash2']]);
		}
	});
	return false;
});
";
        $cs->registerScript('Yii.CCaptcha#' . $id, $js);
    }

    /**
     * Checks if specified graphic extension support is loaded.
     * @param string extension name to be checked. Possible values are 'gd', 'imagick' and null.
     * Default value is null meaning that both extensions will be checked. This parameter
     * is available since 1.1.13.
     * @return boolean true if ImageMagick extension with PNG support or GD with FreeType support is loaded,
     * otherwise false
     * @since 1.1.5
     */
    public static function checkRequirements($extension = null)
    {
        if (extension_loaded('imagick')) {
            $imagick = new Imagick();
            $imagickFormats = $imagick->queryFormats('PNG');
        }
        if (extension_loaded('gd')) {
            $gdInfo = gd_info();
        }
        if ($extension === null) {
            if (isset($imagickFormats) && in_array('PNG', $imagickFormats))
                return true;
            if (isset($gdInfo) && $gdInfo['FreeType Support']) return true;
        } elseif ($extension == 'imagick' && isset($imagickFormats) && in_array('PNG', $imagickFormats))
            return true;
        elseif ($extension == 'gd' && isset($gdInfo) && $gdInfo['FreeType Support'])
            return true;
        return false;
    }

}

/**
 * CCaptchaAction class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CCaptchaAction renders a CAPTCHA image.
 *
 * CCaptchaAction is used together with {@link CCaptcha} and {@link CCaptchaValidator}
 * to provide the {@link http://en.wikipedia.org/wiki/Captcha CAPTCHA} feature.
 *
 * You must configure properties of CCaptchaAction to customize the appearance of
 * the generated image.
 *
 * Note, CCaptchaAction requires PHP GD2 extension.
 *
 * Using CAPTCHA involves the following steps:
 * <ol>
 * <li>Override {@link CController::actions()} and register an action of class CCaptchaAction with ID 'captcha'.</li>
 * <li>In the form model, declare an attribute to store user-entered verification code, and declare the attribute
 * to be validated by the 'captcha' validator.</li>
 * <li>In the controller view, insert a {@link CCaptcha} widget in the form.</li>
 * </ol>
 *
 * @property string $verifyCode The verification code.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.widgets.captcha
 * @since 1.0
 */
class CCaptchaAction
{

    /**
     * The name of the GET parameter indicating whether the CAPTCHA image should be regenerated.
     */
    const REFRESH_GET_VAR = 'refresh';

    /**
     * Prefix to the session variable name used by the action.
     */
    const SESSION_VAR_PREFIX = 'Yii.CCaptchaAction.';

    /**
     *
     * @var integer how many times should the same CAPTCHA be displayed. Defaults to 3.
     *      A value less than or equal to 0 means the test is unlimited (available since version 1.1.2).
     */
    public $testLimit = 3;

    /**
     *
     * @var integer the width of the generated CAPTCHA image. Defaults to 120.
     */
    public $width = 100;

    /**
     *
     * @var integer the height of the generated CAPTCHA image. Defaults to 50.
     */
    public $height = 45;

    /**
     * @var integer padding around the text. Defaults to 2.
     */
    public $padding = 2;

    /**
     *
     * @var integer the background color. For example, 0x55FF00.
     *      Defaults to 0xFFFFFF, meaning white color.
     */
    // public $backColor = 0xFFFFFF;
    public $backColor = 0xFFFFFF;

    /**
     *
     * @var integer the font color. For example, 0x55FF00. Defaults to 0x2040A0 (blue color).
     */
    public $foreColor = 0x2040A0;
    //public $foreColor = 0x55FF00;
    /**
     *
     * @var boolean whether to use transparent background. Defaults to false.
     */
    public $transparent = false;

    /**
     *
     * @var integer the minimum length for randomly generated word. Defaults to 6.
     */
    public $minLength = 5;

    /**
     *
     * @var integer the maximum length for randomly generated word. Defaults to 7.
     */
    public $maxLength = 6;

    /**
     *
     * @var integer the offset between characters. Defaults to -2. You can adjust this property
     *      in order to decrease or increase the readability of the captcha.
     * @since 1.1.7
     *
     */
    public $offset = -1;

    /**
     *
     * @var string the TrueType font file. Defaults to Duality.ttf which is provided
     *      with the Yii release.
     */
    public $fontFile;

    /**
     *
     * @var string the fixed verification code. When this is property is set,
     *      {@link getVerifyCode} will always return this value.
     *      This is mainly used in automated tests where we want to be able to reproduce
     *      the same verification code each time we run the tests.
     *      Defaults to null, meaning the verification code will be randomly generated.
     * @since 1.1.4
     */
    public $fixedVerifyCode;

    /**
     *
     * @var string the graphic extension that will be used to draw CAPTCHA image. Possible values
     *      are 'gd', 'imagick' and null. Null value means that fallback mode will be used: ImageMagick
     *      is preferred over GD. Default value is null.
     * @since 1.1.13
     */
    public $backend = null;

    /**
     * Runs the action.
     */
    public function run($isCreateCode = false)
    {
        $code = '';
        if ($isCreateCode)   // AJAX request for regenerating code
        {
            require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '../CJSON.php';
            /*
              $string = CJSON::encode ( array (
              'hash1' => $this->generateValidationHash ( $code ),
              'hash2' => $this->generateValidationHash ( strtolower ( $code ) )
              // we add a random 'v' parameter so that FireFox can refresh the image
              // when src attribute of image tag is changed

              'url' => $this->getController ()->createUrl ( $this->getId (), array ( 'v' => uniqid () ) )

              ); */
            $code = $this->getVerifyCode(true);
        } else {
            $code = $this->getVerifyCode();
        }

        $this->renderImage($code);
        exit();
    }

    /**
     * Generates a hash code that can be used for client side validation.
     *
     * @param string $code
     *            the CAPTCHA code
     * @return string a hash code generated from the CAPTCHA code
     * @since 1.1.7
     */
    public function generateValidationHash($code)
    {
        for ($h = 0, $i = strlen($code) - 1; $i >= 0; --$i) $h += ord($code [$i]);
        return $h;
    }

    /**
     * Gets the verification code.
     *
     * @param boolean $regenerate
     *            whether the verification code should be regenerated.
     * @return string the verification code.
     */
    public function getVerifyCode($regenerate = false)
    {

        if ($this->fixedVerifyCode !== null) {
            return $this->fixedVerifyCode;
        }
        $key = $this->getSessionKey();
        //生成验证码字符串
        $sessionCaptcha = $this->generateVerifyCode();
        App::$app->session->setSessionArray(array(
            $key => App::$app->session->getSession($key),
            "{$key}count" => 1,
        ));


        return $sessionCaptcha;
    }

    /**
     * Validates the input to see if it matches the generated code.
     *
     * @param string $input
     *            user input
     * @param boolean $caseSensitive
     *            whether the comparison should be case-sensitive
     * @return boolean whether the input is valid
     */
    public function validate($input, $caseSensitive = false)
    {
        $code = $this->getVerifyCode();
        $valid = $caseSensitive ? ($input === $code) : strcasecmp($input, $code) === 0;
        $session = ABaseApplication::getSession();
        $name = $this->getSessionKey() . 'count';
        $authcodeCount = $session [$name] + 1;
        ABaseApplication::setSession($name, $authcodeCount);
        if ($authcodeCount > $this->testLimit && $this->testLimit > 0) {
            $code = $this->getVerifyCode(true);
        }
        return $valid;
    }

    /**
     * Generates a new verification code.
     *
     * @return string the generated verification code
     */
    protected function generateVerifyCode()
    {
        if ($this->minLength < 3) $this->minLength = 3;
        if ($this->maxLength > 20) $this->maxLength = 20;
        if ($this->minLength > $this->maxLength)
            $this->maxLength = $this->minLength;
        $length = mt_rand($this->minLength, $this->maxLength);
        $letters = 'b6c$9df8@ghjkl?5m&3np7q4rs2t1vwxyz!';
        $vowels = 'aeiu';
        $code = '';
        for ($i = 0; $i < $length; ++$i) {
            if ($i % 2 && mt_rand(0, 10) > 2 || !($i % 2) && mt_rand(0, 10) > 9)
                $code .= $vowels [mt_rand(0, 3)];
            else $code .= $letters [mt_rand(0, 34)];
        }
        return $code;
    }

    /**
     * Returns the session variable name used to store verification code.
     *
     * @return string the session variable name
     */
    protected function getSessionKey()
    {
        return 'authcode'; //substr ( md5 ( microtime ( true ) ), - 4, 4 );
    }

// substr ( md5 ( microtime ( true ) ), - 4, 4 );
    /**
     * Renders the CAPTCHA image based on the code using library specified in the {@link $backend} property.
     *
     * @param string $code
     *            the verification code
     */
    protected function renderImage($code)
    {
        if ($this->backend === null && CCaptcha::checkRequirements('imagick') || $this->backend === 'imagick') {
            $this->renderImageImagick($code);
        } else if ($this->backend === null && CCaptcha::checkRequirements('gd') || $this->backend === 'gd') {
            $this->renderImageGD($code);
        }
    }

    /**
     * Renders the CAPTCHA image based on the code using GD library.
     *
     * @param string $code
     *            the verification code
     * @since 1.1.13
     */
    protected function renderImageGD($code)
    {

        // 新建一个真彩色图像
        $image = imagecreatetruecolor($this->width, $this->height);

        //为一幅图像分配颜色
        $backColor = imagecolorallocate($image, (int)($this->backColor % 0x1000000 / 0x10000), (int)($this->backColor % 0x10000 / 0x100), $this->backColor % 0x100);

        //画一矩形并填充
        imagefilledrectangle($image, 0, 0, $this->width, $this->height, $backColor);

        //取消图像颜色的分配
        imagecolordeallocate($image, $backColor);

        if ($this->transparent) {
            //将某个颜色定义为透明色
            imagecolortransparent($image, $backColor);
        }

        //为一幅图像分配颜色
        $foreColor = imagecolorallocate($image, (int)($this->foreColor % 0x1000000 / 0x10000), (int)($this->foreColor % 0x10000 / 0x100), $this->foreColor % 0x100);

        if ($this->fontFile === null)
            $this->fontFile = dirname(__FILE__) . '/Duality.ttf';

        $length = strlen($code);
        $box = imagettfbbox(30, 0, $this->fontFile, $code);
        $w = $box [4] - $box [0] + $this->offset * ($length - 1);
        $h = $box [1] - $box [5];
        $scale = min(($this->width - $this->padding * 2) / $w, ($this->height - $this->padding * 2) / $h);
        $x = 10;
        $y = round($this->height * 27 / 40);
        for ($i = 0; $i < $length; ++$i) {
            $fontSize = (int)(rand(26, 32) * $scale * 0.8);
            $angle = rand(-10, 10);
            $letter = $code [$i];
            $box = imagettftext($image, $fontSize, $angle, $x, $y, $foreColor, $this->fontFile, $letter);
            $x = $box [2] + $this->offset;
        }
        $len = rand(6, 8);
        for ($i = 0; $i < $len; $i++) {
            $c1 = intval($this->foreColor % 0x1000000 / 0x10000);
            $c2 = intval($this->foreColor % 0x10000 / 0x100);
            $c3 = intval($this->foreColor % 0x100);
            //    stop($c1.'|'.$c2.'|'.$c3);
            $foreColorS = imagecolorallocate($image, rand($c1 - 20, $c1 + 40), rand($c2 - 35, $c2 + 50), rand($c3 - 50, $c3 + 35));
            imageline($image, rand(0, $w), rand(0, $h), rand(0, $w), rand(0, $h), $foreColorS);
        }
        imagecolordeallocate($image, $foreColor);
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Transfer-Encoding: binary');
        header("Content-type: image/png");
        imagepng($image);
        imagedestroy($image);
    }

    /**
     * Renders the CAPTCHA image based on the code using ImageMagick library.
     *
     * @param string $code
     *            the verification code
     * @since 1.1.13
     */
    protected function renderImageImagick($code)
    {

        $backColor = new ImagickPixel('#' . dechex($this->backColor));
        $foreColor = new ImagickPixel('#' . dechex($this->foreColor));

        $image = new Imagick ();
        $image->newImage($this->width, $this->height, $backColor);

        if ($this->fontFile === null) {
            $this->fontFile = dirname(__FILE__) . '/Duality.ttf';
        }
        $draw = new ImagickDraw ();
        $draw->setFont($this->fontFile);
        $draw->setFontSize(30);
        $fontMetrics = $image->queryFontMetrics($draw, $code);

        $length = strlen($code);
        $w = (int)($fontMetrics ['textWidth']) - 8 + $this->offset * ($length - 1);
        $h = (int)($fontMetrics ['textHeight']) - 8;
        $scale = min(($this->width - $this->padding * 2) / $w, ($this->height - $this->padding * 2) / $h);
        $x = 10;
        $y = round($this->height * 27 / 40);

//
//        stop(2313);
//        //画干扰线
//        $fillcolor = '#F00F00';
//        $draw->setFillColor($fillcolor);
//        $draw->setStrokeWidth(1);
//        //start point //end point
//        $width     = 601;
//        $height    = 601;
//        $max_x     = $width-1;
//        $max_y     = $height-1;
//        $mid_y     = $max_y/2;
//        $mid_x     = $max_x/2;
//        $co_ords   = array(
//            array('x'=>$mid_x,'y'=>0),
//            array('x'=>0,'y'=>$max_y),
//            array('x'=>0,'y'=>$max_y),
//            array('x'=>$max_x,'y'=>$max_y),
//            array('x'=>$mid_x,'y'=>0),
//            array('x'=>$max_x,'y'=>$max_y)
//        );
//        $draw->polyline($co_ords);
//        $image->drawImage($draw);


        for ($i = 0; $i < $length; ++$i) {
            $draw = new ImagickDraw ();
            $draw->setFont($this->fontFile);
            $draw->setFontSize((int)(rand(26, 32) * $scale * 0.8));
            $draw->setFillColor($foreColor);
            $image->annotateImage($draw, $x, $y, rand(-10, 10), $code [$i]);
            $fontMetrics = $image->queryFontMetrics($draw, $code [$i]);
            $x += (int)($fontMetrics ['textWidth']) + $this->offset;
        }

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Transfer-Encoding: binary');
        header("Content-type: image/png");
        $image->setImageFormat('png');
        echo $image;
    }

}
