<?php
/**
 * JCaptcha class file.
 *
 * @author Nobuo Kihara <softark@gmail.com>
 * @link http://www.softark.net/
 * @copyright Copyright &copy; 2013 softark.net
 */

/**
 * JCaptcha is an extension to CCaptcha.
 * 
 * JCaptcha can render a CAPTCHA image with non alphabetical characters while CCaptcha is only for alphabets.
 * 
 * JCaptcha must be used together with JCaptchaAction to provide its capability.
 *
 * JCaptcha may render a button next to the CAPTCHA image. Clicking on the button
 * will change the type of the characters from non alphabetical to alphabetical
 * and vice versa.
 *
 * @author Nobuo Kihara <softark@gmail.com>
 */
class JCaptcha extends CCaptcha
{
	/**
	 * @var boolean whether to show the button to change the character type.
	 * If false, the type of the characters is fixed to non alphabetical.
	 * If true, the user can select standard alphabetical characters.
	 * Defaults to true.
	 */
	public $showTypeChangeButton = true;

	/**
	 * @var string the label of the type change button.
	 * Defaults to "Japanese Kana/ABC".
	 */
	public $typeChangeButtonLabel = 'かな/ABC';

	/**
	 * @var type boolean whether to use inner CSS for image and the buttons.
	 * Defaults to true.
	 */
	public $useInnerCss = true;
	
	/**
	 * Renders the widget.
	 */
	public function run()
	{
		if(self::checkRequirements('imagick') || self::checkRequirements('gd'))
		{
			$this->renderImage();
			$this->registerClientScript();
			$this->registerCss();
		}
		else
			throw new CException(Yii::t('yii','GD with FreeType or ImageMagick PHP extensions are required.'));
	}

	/**
	 * Renders the CAPTCHA image.
	 */
	protected function renderImage()
	{
		if(!isset($this->imageOptions['id']))
			$this->imageOptions['id']=$this->getId();
		// set the default class for image and the buttons
		if(!isset($this->imageOptions['class']))
			$this->imageOptions['class']='jcaptcha';
		if(!isset($this->buttonOptions['class']))
			$this->buttonOptions['class']='jcaptcha';

		$url=$this->getController()->createUrl($this->captchaAction,array('v'=>uniqid()));
		$alt=isset($this->imageOptions['alt'])?$this->imageOptions['alt']:'';
		echo CHtml::image($url,$alt,$this->imageOptions);
	}

	/**
	 * Registers the nececssary client scripts.
	 */
	public function registerClientScript()
	{
		parent::registerClientScript();

		if ($this->showTypeChangeButton)
		{
			$cs=Yii::app()->clientScript;
			$id=$this->imageOptions['id'];

			$js="";

			$url=$this->getController()->createUrl($this->captchaAction,array(JCaptchaAction::TYPECHANGE_GET_VAR=>true));
			$cs->registerScript('Yii.JCaptcha#'.$id,'// dummy');
			$label=$this->typeChangeButtonLabel;
			$options=$this->buttonOptions;
			if(isset($options['id']))
				$buttonID=$options['id']=$options['id'].'_tc';
			else
				$buttonID=$options['id']=$id.'_button_tc';
			if($this->buttonType==='button')
			{
				$html=CHtml::button($label, $options) . "&nbsp;";
			}
			else
			{
				$html=CHtml::link($label, $url, $options) . "&nbsp;&nbsp;";
			}
			$js="jQuery('#$id').after(".CJSON::encode($html).");";
			$selector="#$buttonID";

			$js.="
$('body').on('click', '$selector', function(event){
	jQuery.ajax({
		url: ".CJSON::encode($url).",
		dataType: 'json',
		cache: false,
		success: function(data) {
			jQuery('#$id').attr('src', data['url']);
			jQuery('body').data('{$this->captchaAction}.hash', [data['hash1'], data['hash2']]);
		}
	});
	event.preventDefault();
});
";
			$cs->registerScript('Yii.JCaptcha#'.$id,$js);
		}
	}

	/**
	 * Registers the inner CSS
	 */
	public function registerCss()
	{
		if ($this->useInnerCss)
		{
			$css = "
img.{$this->imageOptions['class']} {
	border: 1px solid #CCCCCC;
	margin: 4px 8px 8px 0px;";

	if ($this->clickableImage)
	{
		$css .= "
	cursor: pointer;";
	}
	$css .= "
}";
			Yii::app()->clientScript->registerCss('j-captcha-css', $css);
		}
	}
}
