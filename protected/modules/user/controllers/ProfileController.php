<?php
class ProfileController extends Controller
{
    public $defaultAction = 'show';

    /**
     * Lists all models.
     */
    public function actionIndex()
    {
        echo $this->action->id;
        $dataProvider = new CActiveDataProvider('User', array(
            'criteria' => array(
                'condition' => 'status = :status',
                'params'    => array(':status' => User::STATUS_ACTIVE),
                'order'     => 'last_visit DESC',
            )
        ));

        $this->render('index', array('dataProvider' => $dataProvider));
    }

    /**
     * Show User Profile
     * @param string $username
     * @param null $mode
     * @throws CHttpException
     */
    public function actionShow($username = null, $mode = null)
    {
        if ($username == null) {
            if (Yii::app()->user->isGuest) {
                throw new CHttpException(404, Yii::t('user', 'User not found!'));
            }
        }
        $user = User::model()->findByPk((int)Yii::app()->user->id);
        if (!$user) {
            throw new CHttpException(404, Yii::t('user', 'User not found!'));
        }

        $this->render('show', array('user' => $user, 'mode' => $mode));
    }
}
