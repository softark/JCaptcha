<?php
/**
 * JCaptchaAction class file.
 *
 * @author Nobuo Kihara <softark@gmail.com>
 * @link http://www.softark.net/
 * @copyright Copyright &copy; 2013 softark.net
 */

/**
 * JCaptchaAction is an extension to CCaptchaAction.
 * 
 * JCaptchaAction can render a CAPTCHA image with non alphabetical characters while CCaptchaAction is only for alphabets.
 *
 * JCaptchaAction is used together with JCaptcha and {@link CCaptchaValidator}.
 *
 * @author Nobuo Kihara <softark@gmail.com>
 */

class JCaptchaAction extends CCaptchaAction
{
	/**
	 * The name of the GET parameter indicating whether the CAPTCHA type (non alphabetical characters/alphabets) should be changed.
	 */
	const TYPECHANGE_GET_VAR='typechange';

	/**
	 * @var integer the minimum length for randomly generated word. Defaults to 5.
	 */
	public $minLengthJ = 5;

	/**
	 * @var integer the maximum length for randomly generated word. Defaults to 5.
	 */
	public $maxLengthJ = 5;
	
	/**
	 * @var integer the offset between characters. Defaults to 2. You can adjust this property
	 * in order to decrease or increase the readability of the non alphabetical captcha.
	 **/
	public $offsetJ = 2;

	/**
	 * @var boolean whether to use non alphabetical characters. Defaults to true.
	 */
	public $useJChars = true;

	/**
	 * @var string Non alphabetical font file. Defaults to seto-mini.ttf, a subset of
	 * setofont.ttf (http://nonty.net/item/font/setofont.php) created and shared
	 * by 瀬戸のぞみ (Nozomi Seto). Special thanks to Nozomi for the wonderful font.
	 * Note that seto-mini.ttf supports only ASCII, Hirakana and Katakana.
	 */
	public $fontFileJ;
	
	/**
	 * @var boolean whether to render the captcha image with a fixed angle. Defaults to false.
	 * You may want to set this to true if you have trouble rendering your font.
	 */
	public $fixedAngle = false;
	
	/**
	 * @var string The string used for generating the random string of captcha.
	 * Defaults to a series of Japanese Kana characters. You may want to set your own.
	 */
	public $seeds;
	
	/**
	 * @var boolean whether to check if conversion to shift_JIS is needed
	 * Defaults to false.
	 */
	public $checkSJISConversion = false;
	
	/**
	 * Runs the action.
	 */
	public function run()
	{
		// Character type ... defaults to non alphabetical characters
		$session = Yii::app()->session;
		$session->open();
		$name = $this->getSessionKey();
		if ($session[$name . 'type'] !== null && $session[$name . 'type'] === 'abc')
		{
			$this->useJChars = false;
		}

		if (isset($_GET[self::REFRESH_GET_VAR]))  // AJAX request to refresh the code
		{
			$code = $this->getVerifyCode(true);
			// we add a random 'v' parameter so that FireFox can refresh the image
			// when src attribute of image tag is changed
			echo CJSON::encode(array(
				'hash1'=>$this->generateValidationHash($code),
				'hash2'=>$this->generateValidationHash(strtolower($code)),
				// we add a random 'v' parameter so that FireFox can refresh the image
				// when src attribute of image tag is changed
				'url'=>$this->getController()->createUrl($this->getId(),array('v' => uniqid())),
			));
		}
		elseif (isset($_GET[self::TYPECHANGE_GET_VAR]))  // AJAX request to change the character type
		{
			$this->useJChars = ! $this->useJChars;
			$code = $this->getVerifyCode(true);
			echo CJSON::encode(array(
				'hash1'=>$this->generateValidationHash($code),
				'hash2'=>$this->generateValidationHash(strtolower($code)),
				// we add a random 'v' parameter so that FireFox can refresh the image
				// when src attribute of image tag is changed
				'url'=>$this->getController()->createUrl($this->getId(),array('v' => uniqid())),
			));
		}
		else
		{
			$this->renderImage($this->getVerifyCode());
			Yii::app()->end();
		}
	}

	/**
	 * Generates a hash code that can be used for client side validation.
	 * @param string $code the CAPTCHA code
	 * @return string a hash code generated from the CAPTCHA code
	 * 
	 * This generates a hash code from a UTF-8 string that is compatible with CCaptchaAction::generateValidationHash
	 */
	public function generateValidationHash($code)
	{
		$code = mb_convert_encoding($code, 'UCS-2', 'UTF-8');
		$hash = 0;
		for ( $i = strlen($code) / 2 - 1; $i >= 0; --$i)
		{
			$hash += ord($code[$i*2]) * 256 + ord($code[$i*2+1]);
		}
		return $hash;
	}
	
	/**
	 * Gets the verification code.
	 * @param string $regenerate whether the verification code should be regenerated.
	 * @return string the verification code.
	 */
	public function getVerifyCode($regenerate=false)
	{
		if($this->fixedVerifyCode !== null)
			return $this->fixedVerifyCode;

		$session = Yii::app()->session;
		$session->open();
		$name = $this->getSessionKey();
		if($session[$name] === null || $regenerate)
		{
			$session[$name] = $this->generateVerifyCode();
			$session[$name . 'type'] = $this->useJChars ? 'jchars' : 'abc';
			$session[$name . 'count'] = 1;
		}
		return $session[$name];
	}

	/**
	 * Generates a new verification code.
	 * @return string the generated verification code
	 */
	protected function generateVerifyCode()
	{
		// alphabets ?
		if (!$this->useJChars)
		{
			return parent::generateVerifyCode();
		}

		if($this->minLengthJ < 3)
			$this->minLengthJ = 3;
		if($this->maxLengthJ > 20)
			$this->maxLengthJ = 20;
		if($this->minLengthJ > $this->maxLengthJ)
			$this->maxLengthJ = $this->minLengthJ;
		$length = mt_rand($this->minLengthJ, $this->maxLengthJ);

		$letters = isset($this->seeds) ? $this->seeds : 
				'あいうえおかきくけこがぎぐげごさしすせそざじずぜぞたちつてとだぢづでどなにぬねのはひふへほはひふへほはひふへほばびぶべぼぱぴぷぺぽまみむめもやゆよらりるれろわをん';
		$len = mb_strlen($letters, 'UTF-8');

		$code = '';
		for($i = 0; $i < $length; ++$i)
		{
			$code .= mb_substr($letters, mt_rand(0, $len - 1), 1, 'UTF-8');
		}

		return $code;
	}

	/**
	 * Renders the CAPTCHA image based on the code.
	 * @param string $code the verification code
	 * @return string image content
	 */
	protected function renderImage($code)
	{
		// alphabets ?
		if (!$this->useJChars)
		{
			parent::renderImage($code);
			return;
		}

		// font defaults to seto-mini.ttf
		if($this->fontFileJ === null)
			$this->fontFileJ = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'seto-mini.ttf';

		$encoding = 'UTF-8';
		
		// check if conversion to Shift_JIS is needed
		if ($this->checkSJISConversion)
		{
			$gd_info = gd_info();
			$must_use_sjis = $gd_info['JIS-mapped Japanese Font Support'];
			if ($must_use_sjis)
			{
				$code = mb_convert_encoding($code, 'SJIS', 'UTF-8');
				$encoding = 'SJIS';
			}
		}
		
		if($this->backend===null && CCaptcha::checkRequirements('imagick') || $this->backend==='imagick')
			$this->renderImageImagickJ($code, $encoding);
		else if($this->backend===null && CCaptcha::checkRequirements('gd') || $this->backend==='gd')
			$this->renderImageGDJ($code, $encoding);
	}
	
	/**
	 * Renders the CAPTCHA image based on the code using GD.
	 * @param string $code the verification code
	 * @param string $encoding the encoding of the verification code
	 * @return string image content
	 */
	protected function renderImageGDJ($code, $encoding)
	{
		$image = imagecreatetruecolor($this->width,$this->height);

		$backColor = imagecolorallocate($image,
				(int)($this->backColor % 0x1000000 / 0x10000),
				(int)($this->backColor % 0x10000 / 0x100),
				$this->backColor % 0x100);
		imagefilledrectangle($image,0,0,$this->width,$this->height,$backColor);
		imagecolordeallocate($image,$backColor);

		if($this->transparent)
			imagecolortransparent($image,$backColor);

		$foreColor = imagecolorallocate($image,
				(int)($this->foreColor % 0x1000000 / 0x10000),
				(int)($this->foreColor % 0x10000 / 0x100),
				$this->foreColor % 0x100);

		$length = mb_strlen($code, $encoding);
		$box = imagettfbbox(30,0,$this->fontFileJ,$code);
		$w = $box[4] - $box[0] + $this->offsetJ * ($length - 1);
		$h = $box[1] - $box[5];
		if ($h <= 0)
		{
			$h = $w / $length;
		}
		$scale = min(($this->width - $this->padding * 2) / $w, ($this->height - $this->padding * 2) / $h);
		$x = 8;
		// font size and angle
		$fontSize = (int)(30 * $scale * 0.90);
		$angle = 0;
		// base line
		$ybottom = $this->height - $this->padding * 4;
		$ytop = (int)($h * $scale * 0.95) + $this->padding * 4;
		if ($ytop > $ybottom)
		{
			$ytop = $ybottom;
		}
		for($i = 0; $i < $length; ++$i)
		{
			$letter = mb_substr( $code, $i, 1, $encoding);
			$y = mt_rand($ytop, $ybottom);
			if (!$this->fixedAngle)
				$angle = mt_rand(-15, 15);
			$box = imagettftext($image,$fontSize,$angle,$x,$y,$foreColor,$this->fontFileJ,$letter);
			$x = $box[2] + $this->offsetJ;
		}

		imagecolordeallocate($image,$foreColor);

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Transfer-Encoding: binary');
		header("Content-type: image/png");
		imagepng($image);
		imagedestroy($image);
	}

	/**
	 * Renders the CAPTCHA image based on the code using Imagick.
	 * @param string $code the verification code
	 * @param string $encoding the encoding of the verification code
	 * @return string image content
	 */
	protected function renderImageImagickJ($code, $encoding)
	{
		$backColor = new ImagickPixel('#'.dechex($this->backColor));
		$foreColor = new ImagickPixel('#'.dechex($this->foreColor));

		$image = new Imagick();
		$image->newImage($this->width, $this->height, $backColor);

		$draw = new ImagickDraw();
		$draw->setFont($this->fontFileJ);
		$draw->setFontSize(30);
		$fontMetrics=$image->queryFontMetrics($draw, $code);

		$length = mb_strlen($code, $encoding);
		$w = (int)($fontMetrics['textWidth']) + $this->offsetJ * ($length-1);
		$h = (int)($fontMetrics['textHeight']);
		$scale = min(($this->width - $this->padding*2) / $w, ($this->height - $this->padding*2) / $h);
		$x=8;
		// font size and angle
		$fontSize = (int)(30 * $scale * 0.90);
		$angle = 0;
		// base line
		$ybottom = $this->height - $this->padding * 4;
		$ytop = (int)($h * $scale * 0.95) + $this->padding * 4;
		if ($ytop > $ybottom)
		{
			$ytop = $ybottom;
		}
		for($i = 0; $i < $length; ++$i)
		{
			$letter = mb_substr( $code, $i, 1, $encoding);
			$y = mt_rand($ytop, $ybottom);
			if (!$this->fixedAngle)
				$angle = mt_rand(-15, 15);
			$draw = new ImagickDraw();
			$draw->setFont($this->fontFileJ);
			$draw->setFontSize($fontSize);
			$draw->setFillColor($foreColor);
			$image->annotateImage($draw, $x, $y, $angle, $letter);
			$fontMetrics = $image->queryFontMetrics($draw, $letter);
			$x += (int)($fontMetrics['textWidth']) + $this->offsetJ;
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