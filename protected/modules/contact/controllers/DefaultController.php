<?php
/**
 * User: fad
 * Date: 06.09.12
 * Time: 18:39
 */
class DefaultController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=> array(
				'class' => 'CCaptchaAction', 'backColor' => 0xFFFFFF,
			), // page action renders "static" pages stored under 'protected/views/site/pages'
		);
	}

	/**
	 * Displays the contact page
	 */
	public function actionIndex()
	{
		$model = new ContactForm;
		#$this->performAjaxValidation($model);
		if ( isset($_POST['ContactForm']) )
		{
			$model->attributes = $_POST['ContactForm'];

			if ( $model->validate() )
			{
				$headers = "From: {$model->email}\r\nReply-To: {$model->email}\r\nContent-type: text/plain;charset=utf-8";
				if ( !$model->subject )
					$model->subject = Yii::t('contact', '������ � ����� '.Yii::app()->name);
				$body = '';
				foreach ( $model->attributes as $attribute => $value )
				{
					if ( in_array($attribute, array('verifyCode')) )
						continue;
					if ( $value )
						$body .= $model->getAttributeLabel($attribute).": ".$value."\r\n\r\n";
				}

				mail(Yii::app()->params['adminEmail'], $model->subject, $body, $headers);
				Yii::app()->user->setFlash('success', Yii::t('contact', '������� �� ���������! �� ��� ����������� �������!'));
				$this->refresh();
			}
		}
		$this->render('contact', array('model'=> $model));
	}
}
