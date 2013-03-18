JCaptcha
========

Captcha for Yii-framework that can render non-alphabetic characters. It's an extension of CCaptcha.

![JCaptcha in Action](docs/jcaptcha.png "JCaptcha in Action")

[日本語の README](README-ja.md)

Requirements
------------
+ Yii Version 1.1.13 or later
+ PHP GD + FreeType extension

Usage
-----
1. Place "jcaptcha" directory that contains 3 files in it under the extensions directory of the application.

		protected
		  extensions
		    jcaptcha
		      JCaptcha.php
		      JCaptchaAction.php
		      setofontmaru.ttf

2. Replace "CCaptcha" with "ext.jcaptcha.JCaptcha" in your view script.

		<?php
		/* $this->widget('CCaptcha'); */
		$this->widget('ext.jcaptcha.JCaptcha');
		?>

3. Replace "CCaptchaAction" with "ext.jcaptcha.JCaptchaAction" in your controller.

		public function actions()
		{
			return array(
				// captcha action renders the CAPTCHA image displayed on the contact page
				'captcha'=>array(
					/* 'class'=>'CCaptchaAction', */
					'class'=>'ext.jcaptcha.JCaptchaAction',
					'backColor'=>0xFFFFFF,
				),
				...
			);
		}

Properties of JCaptcha
----------------------
JCaptcha supports all the properties of CCaptcha and the following additional ones.
The items with **(*)** are basic options that you may want to configure.

1. **showTypeChangeButton (*)**

	@var boolean  
	Whether to show the button to change the character type. Defaults to true.  
	If false, the type of the characters is fixed to non-alphabet characters.
	If true, the user can select standard alphabet characters.

2. **typeChangeButtonLabel (*)**

	@var string  
	The label of the type change button. Defaults to "かな/ABC" ... "Japanese Kana/ABC".  
	You may want to change it if you want non-Japanese characters.

3. useInnerCss

	@var boolean  
	Whether to use inner CSS for image and the buttons.	Defaults to true.

Properties of JCaptchaAction
----------------------------
JCaptchaAction supports all the properties of CCaptchaAction and the following additional ones.
The items with **(*)** are basic options that you may want to configure.

1. **minLengthJ (*)**

	@var integer  
	The minimum length for randomly generated word.	Defaults to 5

2. **maxLengthJ (*)**

	@var integer  
	The maximum length for randomly generated word.	Defaults to 5

3. **seeds (*)**

	@var string  
	The string used for generating the random word. Defaults to a series of Japanese Kana characters.  
	You may want to set your own.

4. **fontFileJ (*)**

	@var string  
	The font to be used for non-alphabetic characters. Defaults to setofontmaru.ttf.  
	Note that the default font only supports Japanese Hirakana and Katakana.
	You have to provide an appropriate font file if you want to render your choice of characters.

5. offsetJ

	@var integer  
	The offset between characters. Defaults to 2.  
	You can adjust this property in order to decrease or increase the readability of the non-alphabetic captcha.

6. fixedAngle

	@var boolean  
	Whether to render the non-alphabetic captcha image with a fixed angle. Defaults to false.  
	You may want to set this to true if you have trouble rendering your font.

7. checkSJISConversion

	@var boolean  
	Whether to check if conversion to shift_JIS is needed. Defaults to false.

History
-------

+ Version 1.0.0 (2013-03-18)
	+ Initial release
+ Version 1.0.1 (2013-03-18)
	+ Bug fix

Acknowledgment
--------------
Many thanks to [瀬戸のぞみ (Nozomi Seto)](http://nonty.net/about/) for the wonderful work of [瀬戸フォント丸 (setofontmaru.ttf)](http://nonty.net/item/font/setofontmaru.php).

