Captcha that renders non-alphabetic characters. It's an extension of [CCaptcha].

![JCaptcha in Action](http://tools.softark.net/images/jcaptcha.png "JCaptcha in Action")

![JCaptcha using Chinese characters](http://tools.softark.net/images/jcaptcha-c.png "JCaptcha using Chinese characters")

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

		[php]
		<?php
		/* $this->widget('CCaptcha'); */
		$this->widget('ext.jcaptcha.JCaptcha');
		?>

3. Replace "CCaptchaAction" with "ext.jcaptcha.JCaptchaAction" in your controller.

		[php]
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
JCaptcha supports all the properties of [CCaptcha] and the following additional ones.
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

How to Customize
----------------

The following is a sample code that shows how to customize JCaptcha and JCaptchaAction.
It shows Chinese characters for the captcha.

In the view script:

~~~
[php]
<div class="row">
<?php echo $form->labelEx($model,'verifyCode')) ?>
<?php $this->widget('ext.jcaptcha.JCaptcha', array(
	'clickableImage' => true,
	'showRefreshButton'=> false,
	'showTypeChangeButton' => true,
	'buttonType' => 'link',
	'typeChangeButtonLabel' => '漢字/ABC',
	'imageOptions' => array(
		'width' => 120,
		'height' => 50,
		'title' => '请单击取得新的编码',
	)) ); ?>
<?php echo $form->textField($model,'verifyCode'); ?>
<?php echo $form->error($model,'verifyCode') ?>
<p class="hint">請輸入被表示的文字。</p>
</div>
~~~

And in the controller:

~~~
[php]
	public function actions()
	{
		return array(
			'captcha' => array(
				'class' => 'ext.jcaptcha.JCaptchaAction',
				'seeds' => '几乎所有的应用程序都是建立在数据库之上虽然可以非常灵活的操作数据库但有些时候一些设计的选择可以使它更便于使用首先应用程序广泛使用了设计的考虑主要围绕优化使用而不是组成复杂语句实际上大多的设计是使用友好的模式来解决实践中的问题最常用的方式是创建易于被人阅读和理解的代码例如使用命名来传达意思但是这很难做到',
				'fontFileJ' => Yii::getPathOfAlias('ext.jcaptcha') . '/gbsn00lp.ttf',
				'backColor' => 0xFFFFFF,
			),
		);
	}
~~~

Note that the sample code assumes that you have placed your choice of font file ("gbsn00lp.ttf") in the same directory as the extension.

You have to be careful not to include characters in "seeds" that are not supported by your font.

Resources
---------

+ [github repo](https://github.com/softark/JCaptcha)

History
-------

+ Version 1.0.0 (2013-03-18)
	+ Initial release
+ Version 1.0.1 (2013-03-18)
	+ Bug fix

Acknowledgment
--------------
Many thanks to [瀬戸のぞみ (Nozomi Seto)](http://nonty.net/about/) for the wonderful work of [瀬戸フォント丸 (setofontmaru.ttf)](http://nonty.net/item/font/setofontmaru.php).

