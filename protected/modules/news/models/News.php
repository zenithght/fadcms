<?php

/**
 * This is the model class for table "{{news}}".
 *
 * The followings are the available columns in table '{{news}}':
 * @property integer $id
 * @property string $date
 * @property string $title
 * @property string $keywords
 * @property string $description
 * @property string $content_short
 * @property string $content
 * @property string $slug
 * @property string $image
 * @property integer $status
 * @property integer $is_protected
 * @property integer $create_user_id
 * @property integer $update_user_id
 * @property string $create_time
 * @property string $update_time
 *
 * The followings are the available model relations:
 * @property User $author
 */
class News extends CActiveRecord
{
    public $author_search;
    const STATUS_DRAFT      = 0;
    const STATUS_PUBLISHED  = 1;
    const STATUS_MODERATION = 2;

    const PROTECTED_NO  = 0;
    const PROTECTED_YES = 1;

    public $versions = array(
        'thumb'    => array( //thumb is required
            'resize' => array(130, null),
        ),
        'small'    => array(
            'resize' => array(320, null),
        ),
        'standard' => array(
            'resize' => array(640, null),
        ),
        'medium'   => array(
            'resize' => array(1024, null),
        ),
    );

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return News the static model class
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
        return '{{news}}';
    }

    public function behaviors()
    {
        return array(
            'comments' => array(
                'class' => 'application.modules.comment.behaviors.CommentBehavior',
            )
        );
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('date, title, content_short', 'required', 'on' => array('update', 'insert')),
            array('status, is_protected, create_user_id, update_user_id', 'numerical', 'integerOnly' => true),
            array('status', 'in', 'range' => array_keys($this->getStatusList())),
            array('title, keywords, slug', 'length', 'max' => 200),
            array('content_short', 'length', 'max' => 400),
            array('description', 'length', 'max' => 250),
            array('image', 'length', 'max' => 300),
            array('slug', 'unique'),
            array(
                'image',
                'file',
                'types'      => Yii::app()->getModule('news')->uploadAllowExt,
                'allowEmpty' => true,
                'safe'       => false
            ),
            array('title, keywords, description, content_short, content, slug', 'filter', 'filter' => 'trim'),
            array(
                'title, slug, keywords, description',
                'filter',
                'filter' => array($obj = new CHtmlPurifier(), 'purify')
            ),
            array(
                'slug',
                'match',
                'pattern' => '/^[a-zA-Z0-9_\-]+$/',
                'message' => Yii::t('news', 'Строка содержит запрещенные символы: {attribute}')
            ),
            array(
                'id, date, title, keywords, description, slug, content_short, content, status, is_protected, create_user_id, update_user_id, create_time, update_time,   author_search',
                'safe',
                'on' => 'search'
            ),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'author' => array(self::BELONGS_TO, 'User', 'create_user_id'),
            'changeAuthor' => array(self::BELONGS_TO, 'User', 'update_user_id'),
        );
    }

    /**
     * @return array
     */
    public function scopes()
    {
        return array(
            'published' => array(
                'condition' => 'status = :status',
                'params'    => array(':status' => self::STATUS_PUBLISHED)
            ),
            'protected' => array(
                'condition' => 'is_protected = :is_protected',
                'params'    => array(':is_protected' => self::PROTECTED_YES)
            ),
            'public'    => array(
                'condition' => 'is_protected = :is_protected',
                'params'    => array(':is_protected' => self::PROTECTED_NO)
            ),
            'recent'    => array(
                'order' => 'create_time DESC',
                'limit' => 5,
            ),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id'            => 'ID',
            'date'          => Yii::t('news', 'Дата'),
            'title'         => Yii::t('news', 'Заголовок'),
            'keywords'      => Yii::t('news', 'Ключевые слова (Seo)'),
            'description'   => Yii::t('news', 'Описание (Seo)'),
            'content_short' => Yii::t('news', 'Превью'),
            'content'       => Yii::t('news', 'Текст'),
            'slug'          => Yii::t('news', 'Ссылка'),
            'image'         => Yii::t('news', 'Изображение'),
            'status'        => Yii::t('news', 'Статус'),
            'is_protected'  => Yii::t('news', 'Доступ только для авторизованных пользователей'),
            'author_search' => Yii::t('page', 'Автор'),
            'create_user_id'=> Yii::t('news', 'Автор'),
            'update_user_id'=> Yii::t('news', 'Изменил'),
            'create_time'   => Yii::t('news', 'Создано'),
            'update_time'   => Yii::t('news', 'Изменено'),
        );
    }

    public function beforeValidate()
    {
        if (!$this->slug) {
            $this->slug = Text::translit($this->title);
        }
        return parent::beforeValidate();
    }

    public function beforeSave()
    {
        $this->date = date('Y-m-d', strtotime($this->date));

        if ($imageFile = CUploadedFile::getInstance($this, 'image')) {
            $uploadPath = $this->getUploadPath();
            if (!$this->isNewRecord && is_dir($uploadPath)) {
                $this->deleteImage($uploadPath); // удаляем старое изображение, если обновляем новость
            }
            mkdir($uploadPath, 0777);
            $this->image = pathinfo($imageFile->getName(), PATHINFO_FILENAME) . '.jpg';
            $this->setImage($imageFile->getTempName(), $uploadPath);
        }
        unset($this->update_time);//on update CURRENT_TIMESTAMP
        $this->update_user_id = Yii::app()->user->id;
        if ($this->isNewRecord) {
            $this->create_time    = new CDbExpression('NOW()');
            $this->create_user_id = $this->update_user_id;
        }

        return parent::beforeSave();
    }

    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            $this->deleteImage($this->getUploadPath()); // удалили модель? удаляем и файл
            return true;
        }
        return false;
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->date = date('d.m.Y', strtotime($this->date));
    }

    private function setImage($path, $uploadPath)
    {
        /** @var $image Image */
        $image = Yii::app()->image->load($path);

        /** resize image to Max width x height */
        $image->cresize(Yii::app()->getModule('news')->maxWidth, Yii::app()->getModule('news')->maxHeight);
        $image->save($uploadPath . '/' . $this->getFileName('') . '.jpg');

        // set thumb max width from module setting
        $this->versions['thumb']['resize'][0] = Yii::app()->getModule('news')->thumbMaxWidth;
        /** resize images to user versions and put-sort it to versions-named folders */
        foreach ($this->versions as $version => $actions) {
            $image = Yii::app()->image->load($path);
            foreach ($actions as $method => $args) {
                # if it width >= version->width image no need to resize
                if ($image->width >= $args['0']) {
                    if (!is_dir($uploadPath . DIRECTORY_SEPARATOR . $version)) {
                        mkdir($uploadPath . DIRECTORY_SEPARATOR . $version);
                    }
                    call_user_func_array(array($image, $method), $args);
                    $image->save(
                        $uploadPath . DIRECTORY_SEPARATOR . $version . DIRECTORY_SEPARATOR . $this->getFileName(
                        ) . '.jpg'
                    );
                }
            }
        }
    }

    private function deleteImage($uploadPath)
    {
        $this->removeFile($uploadPath . DIRECTORY_SEPARATOR . $this->getFileName() . '.jpg');

        foreach ($this->versions as $version => $actions) {
            $this->removeFile(
                $uploadPath . DIRECTORY_SEPARATOR . $version . DIRECTORY_SEPARATOR . $this->getFileName() . '.jpg'
            );
            $this->removeDir($uploadPath . DIRECTORY_SEPARATOR . $version);
        }
        $this->removeDir($uploadPath);
    }

    public function renamePath($newPathName)
    {
        if (is_dir($this->getUploadPath())) {
            rename(
                $this->getUploadPath(),
                Yii::app()->getModule('news')->uploadPath . DIRECTORY_SEPARATOR . $newPathName
            );
        }
    }

    private function removeFile($fileName)
    {
        if (file_exists($fileName)) {
            @unlink($fileName);
        }
    }

    private function removeDir($dirName)
    {
        if (is_dir($dirName)) {
            rmdir($dirName);
        }
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        $criteria       = new CDbCriteria;
        $criteria->with = array('author');

        $criteria->compare('id', $this->id);
        $criteria->compare('date', $this->date, true);
        $criteria->compare('title', $this->title, true);
        $criteria->compare('keywords', $this->keywords, true);
        $criteria->compare('description', $this->description, true);
        $criteria->compare('content_short', $this->content_short, true);
        $criteria->compare('content', $this->content, true);
        $criteria->compare('slug', $this->slug, true);
        $criteria->compare('image', $this->image, true);
        if ($this->status != '') {
            $criteria->compare('status', $this->status);
        }
        $criteria->compare('is_protected', $this->is_protected);
        $criteria->compare('create_time', $this->create_time, true);
        $criteria->compare('update_time', $this->update_time, true);
        $criteria->compare(
            'author.username',
            $this->author_search,
            true
        );

        $sort               = new CSort;
        $sort->defaultOrder = 't.date DESC';
        $sort->attributes   = array(
            'author_search' => array(
                'asc' => 'author.username',
                'desc' => 'author.username DESC',
            ),
            '*',
        );
        return new CActiveDataProvider($this, array(
            'criteria'   => $criteria,
            'sort'       => $sort
        ));
    }

    public function getStatusList()
    {
        return array(
            self::STATUS_DRAFT      => Yii::t('news', 'Черновик'),
            self::STATUS_PUBLISHED  => Yii::t('news', 'Опубликовано'),
            self::STATUS_MODERATION => Yii::t('news', 'На модерации')
        );
    }

    public function getStatus()
    {
        $data = $this->getStatusList();
        return array_key_exists($this->status, $data) ? $data[$this->status] : Yii::t('news', 'неизвестно');
    }


    public function getProtectedStatusList()
    {
        return array(
            self::PROTECTED_NO  => Yii::t('news', 'нет'),
            self::PROTECTED_YES => Yii::t('news', 'да')
        );
    }

    public function getProtectedStatus()
    {
        $data = $this->getProtectedStatusList();
        return array_key_exists($this->is_protected, $data) ? $data[$this->is_protected] : Yii::t('news', 'неизвестно');
    }

    /**
     * @return string $module->uploadPath.'/'.$this->slug
     */
    private function getUploadPath()
    {
        return Yii::app()->getModule('news')->uploadPath . DIRECTORY_SEPARATOR . $this->slug;
    }

    private function getFileName()
    {
        return pathinfo($this->image, PATHINFO_FILENAME);
    }

    public function getThumbnailUrl()
    {
        if ($this->image) {
            return Yii::app()->baseUrl . '/' . Yii::app()->getModule('admin')->uploadDir . '/' . Yii::app()->getModule(
                'news'
            )->uploadDir . '/' . $this->slug . '/thumb/' . $this->image;
        }

        return false;
    }

    public function getImageUrl()
    {
        if ($this->image) {
            return Yii::app()->baseUrl . '/' . Yii::app()->getModule('admin')->uploadDir . '/' . Yii::app()->getModule(
                'news'
            )->uploadDir . '/' . $this->slug . '/' . $this->image;
        }

        return false;
    }
}
