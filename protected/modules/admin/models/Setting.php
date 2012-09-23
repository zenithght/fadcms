<?php

/**
 * This is the model class for table "Settings".
 *
 * The followings are the available columns in table 'Settings':
 * @property integer $id
 * @property integer $module_id
 * @property string $key
 * @property string $value
 * @property string $creation_date
 * @property string $change_date
 * @property integer $user_id
 *
 * The followings are the available model relations:
 * @property User $user
 */
class Setting extends CActiveRecord
{
    /** @var string value attribute dynamic label */
    public $label;

    /** @var string html tag for value */
    public $tag = 'textField';

    /** @var array array of values for DropDownRow etc. */
    public $data = array();

    /** @var array htmlOptions for setting */
    public $htmlOptions = array();

    /**
     * Returns the static model of the specified AR class.
     * @param string $className
     * @return Setting the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{settings}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('module_id, key', 'required'),
            array('module_id, key', 'length', 'max' => 32),
            array('value', 'length', 'max' => 255),
            array('user_id', 'numerical', 'integerOnly' => true),
            array('module_id, key', 'match', 'pattern' => '/^[\w\_-]+$/'),
            array('id, module_id, key, value, creation_date, change_date, user_id', 'safe', 'on' => 'search'),
        );
    }

    public function beforeSave()
    {
        $this->change_date = new CDbExpression('NOW()');
        if ($this->isNewRecord) {
            $this->creation_date = $this->change_date;
        }

        if (!isset($this->user_id)) {
            $this->user_id = Yii::app()->user->id;
        }

        return parent::beforeSave();
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'user' => array(self::BELONGS_TO, 'User', 'user_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id'            => Yii::t('setting', 'ID'),
            'module_id'     => Yii::t('setting', 'Модуль'),
            'key'           => Yii::t('setting', 'Ключ'),
            'value'         => Yii::t('setting', 'Значение'),
            'creation_date' => Yii::t('setting', 'Создано'),
            'change_date'   => Yii::t('setting', 'Изменено'),
            'user_id'       => Yii::t('setting', 'Автор'),
        );
    }

    public function getAttributeLabel($attribute)
    {
        return ($attribute == 'value' && isset($this->label)) ? $this->label : parent::getAttributeLabel($attribute);
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id, true);
        $criteria->compare('module_id', $this->module_id, true);
        $criteria->compare('key', $this->key, true);
        $criteria->compare('value', $this->value, true);
        $criteria->compare('creation_date', $this->creation_date, true);
        $criteria->compare('change_date', $this->change_date, true);
        $criteria->compare('user_id', $this->user_id, true);

        return new CActiveDataProvider(get_class($this), array(
            'criteria' => $criteria,
        ));
    }

    public function getSettings($module_id, $keys)
    {
        $settings = array();
        if ($module_id) {
            $criteria = new CDbCriteria();
            $criteria->compare('module_id', $module_id);

            if (is_array($keys)) {
                $criteria->addInCondition('`key`', $keys);
            } else {
                $criteria->compare('`key`', $keys);
            }

            $dependency = new CDbCacheDependency('SELECT UNIX_TIMESTAMP(MAX(change_date)) FROM ' . $this->tableName(
            ) . ' WHERE module_id="' . $module_id . '"');
            $settingsRows = $this->cache(Yii::app()->getModule('admin')->cachingDuration, $dependency, 2)->findAll(
                $criteria
            );
            foreach ($settingsRows as $setting) {
                $settings[$setting->key] = $setting;
            }
        }
        return $settings;
    }
}
