<?php

/**
 * This is the model class for table "{{comment}}".
 *
 * The followings are the available columns in table '{{comment}}':
 * @property integer $id
 * @property string $model
 * @property integer $model_id
 * @property string $content
 * @property string $ip
 * @property string $status
 * @property string $username
 * @property integer $create_user_id
 * @property integer $update_user_id
 * @property string $create_time
 * @property string $update_time
 *
 * The followings are the available model relations:
 * @property User $user
 *
 * The followings are the available model behaviors:
 * @property StatusBehavior $statusMain
 */
class Comment extends CActiveRecord
{
    public $verifyCode;

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Comment the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return CommentModule the comment module instance
     */
    public function getModule()
    {
        return Yii::app()->getModule('comment');
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{comment}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('model, model_id, content', 'required'),
            array('username', 'checkUsername'),
            array('model_id, create_user_id, update_user_id', 'numerical', 'integerOnly' => true),
            array('model', 'length', 'max' => 16),
            array('ip, username', 'length', 'max' => 20),
            array('model, content, ip, username', 'filter', 'filter' => 'trim'),
            array('status', 'in', 'range' => array_keys($this->statusMain->getList())),
            array('model, content, username', 'filter', 'filter' => array($obj = new CHtmlPurifier(), 'purify')),
            array('verifyCode', 'captcha', 'allowEmpty' => !CCaptcha::checkRequirements() || !Yii::app()->user->isGuest),
            array('id, model, model_id, content, ip, status, create_user_id, update_user_id, create_time, update_time', 'safe', 'on' => 'search'),
        );
    }

    /**
     * Returns a list of behaviors that this model should behave as.
     * @return array the behavior configurations (behavior name=>behavior configuration)
     */
    public function behaviors()
    {
        return array(
            'SaveBehavior' => array(
                'class' => 'application.components.behaviors.SaveBehavior',
            ),
            'statusMain' => array(
                'class' => 'application.components.behaviors.StatusBehavior',
                'list' => array(
                    'not_approved' => Yii::t('CommentModule.comment', 'Not approved'),
                    'approved'     => Yii::t('CommentModule.comment', 'Approved'),
                    'spam'         => Yii::t('CommentModule.comment', 'Spam'),
                    'deleted'      => Yii::t('CommentModule.comment', 'Deleted')
                )
            )
        );
    }

    public function checkUsername()
    {
        if ( empty($this->username) && Yii::app()->user->isGuest ) {
            $this->addError('username', Yii::t('yii', '{attribute} cannot be blank.', array('{attribute}' => $this->getAttributeLabel('username'))));
        }
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'user' => array(self::BELONGS_TO, 'User', 'create_user_id'),
        );
    }

    public function scopes()
    {
        return array(
            'new'      => array(
                'condition' => 'status = "not_approved"'
            ),
            'approved' => array(
                'condition' => 'status = "approved"',
                'order'     => 'create_time DESC'
            ),
            'authored' => array(
                'condition' => 'create_user_id is not null'
            )
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'model'          => Yii::t('CommentModule.comment', 'Model'),
            'model_id'       => Yii::t('CommentModule.comment', 'Model'),
            'content'        => Yii::t('CommentModule.comment', 'Comment Text'),
            'status'         => Yii::t('CommentModule.comment', 'Status'),
            'username'       => Yii::t('CommentModule.comment', 'Name'),
            'create_user_id' => Yii::t('CommentModule.comment', 'User'),
            'update_user_id' => Yii::t('CommentModule.comment', 'Change User'),
            'create_time'    => Yii::t('CommentModule.comment', 'Create Time'),
            'update_time'    => Yii::t('CommentModule.comment', 'Update Time'),
            'verifyCode'     => Yii::t('CommentModule.comment', 'Verify Code'),
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id, true);
        $criteria->compare('model', $this->model, true);
        $criteria->compare('model_id', $this->model_id, true);
        $criteria->compare('content', $this->content, true);
        $criteria->compare('ip', $this->ip, true);
        $criteria->compare('status', $this->status);
        $criteria->compare('username', $this->username);
        $criteria->compare('create_user_id', $this->create_user_id, true);
        $criteria->compare('update_user_id', $this->update_user_id, true);
        $criteria->compare('create_time', $this->create_time, true);
        $criteria->compare('update_time', $this->update_time, true);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    public function beforeSave()
    {
        if (parent::beforeSave() && $this->isNewRecord) {
            $this->ip = Yii::app()->request->userHostAddress;
            return true;
        }
        return true;
    }

    /**
     * @return string get comment users name
     */
    public function getUsername()
    {
        return is_null($this->user) ? $this->username : $this->user->{$this->module->usernameAttribute};
    }

    /**
     * @return string get comment users email
     */
    public function getUserEmail()
    {
        return is_null($this->user) ? 'nobody@example.com' : $this->user->{$this->module->userEmailAttribute};
    }
}
