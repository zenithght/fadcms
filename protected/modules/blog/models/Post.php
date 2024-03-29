<?php

/**
 * This is the model class for table "{{blog_post}}".
 *
 * The followings are the available columns in table '{{blog_post}}':
 * @property integer $id
 * @property integer $blog_id
 * @property string $title
 * @property string $keywords
 * @property string $description
 * @property string $content
 * @property string $slug
 * @property string $link
 * @property string $status
 * @property boolean $comment_status
 * @property string $access_type
 * @property integer $create_user_id
 * @property integer $update_user_id
 * @property integer $publish_time
 * @property integer $create_time
 * @property integer $update_time
 *
 * The followings are the available model relations:
 * @property User $createUser
 * @property User $updateUser
 * @property Blog $blog
 *
 * The followings are the available model behaviors:
 * @property StatusBehavior $statusMain
 * @property StatusBehavior $statusAccess
 *
 * @method getComments() return Comment[]
 * @method getTags() return array Post Tags
 * @method setTags() Set one or more tags.
 */
class Post extends CActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Post the static model class
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
        return '{{blog_post}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('blog_id, title, content, publish_time', 'required', 'except' => 'search'),
            array('blog_id, comment_status, create_user_id, update_user_id', 'numerical', 'integerOnly' => true),
            array('title, slug', 'length', 'max' => 75),
            array('keywords, description, link', 'length', 'max' => 200),
            array('slug', 'unique'), #slug is a URL address
            array('link', 'url'),
            array('comment_status', 'boolean'),
            array('access_type', 'in', 'range' => array_keys($this->statusAccess->getList())),
            array('status', 'in', 'range' => array_keys($this->statusMain->getList())),
            array('slug', 'unique'),
            array(
                'title, keywords, description, slug, link, publish_time',
                'filter',
                'filter' => array($obj = new CHtmlPurifier(), 'purify')
            ),
            array(
                'id, blog_id, title, keywords, description, slug, content, link, status, comment_status, access_type, create_user_id, update_user_id, publish_time, create_time, update_time',
                'safe',
                'on' => 'search'
            ),
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
            'syncTranslit' => array(
                'class' => 'ext.syncTranslit.SyncTranslitBehavior',
            ),
            'statusMain' => array(
                'class' => 'application.components.behaviors.StatusBehavior'
            ),
            'statusAccess' => array(
                'class' => 'application.components.behaviors.StatusBehavior',
                'attribute' => 'access_type',
                'list' => array(
                    'public' => Yii::t('BlogModule.blog', 'Public'),
                    'private' => Yii::t('BlogModule.blog', 'Private')
                )
            ),
            'tags' => array(
                'class'                => 'blog.extensions.taggable.ETaggableBehavior',
                'tagTable'             => Yii::app()->db->tablePrefix . 'tag',
                'tagBindingTable'      => Yii::app()->db->tablePrefix . 'blog_post_tag',
                'modelTableFk'         => 'post_id',
                'tagBindingTableTagId' => 'tag_id',
                'cacheID'              => 'cache',
            ),
            'comments' => array(
                'class' => 'application.modules.comment.behaviors.CommentBehavior',
            )
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'createUser' => array(self::BELONGS_TO, 'User', 'create_user_id'),
            'updateUser' => array(self::BELONGS_TO, 'User', 'update_user_id'),
            'blog'       => array(self::BELONGS_TO, 'Blog', 'blog_id'),
        );
    }

    public function scopes()
    {
        return array(
            'published' => array(
                'condition' => 'status = "published" AND publish_time < ' . new CDbExpression('NOW()')
            ),
            'public'    => array(
                'condition' => 't.access_type = "public"',
            ),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id'             => Yii::t('BlogModule.blog', 'ID'),
            'blog_id'        => Yii::t('BlogModule.blog', 'Blog'),
            'title'          => Yii::t('BlogModule.blog', 'Title'),
            'keywords'       => Yii::t('BlogModule.blog', 'Keywords'),
            'description'    => Yii::t('BlogModule.blog', 'Description'),
            'content'        => Yii::t('BlogModule.blog', 'Content'),
            'slug'           => Yii::t('BlogModule.blog', 'URL'),
            'link'           => Yii::t('BlogModule.blog', 'Link'),
            'status'         => Yii::t('BlogModule.blog', 'Status'),
            'comment_status' => Yii::t('BlogModule.blog', 'Comment Status'),
            'access_type'    => Yii::t('BlogModule.blog', 'Access Type'),
            'create_user_id' => Yii::t('BlogModule.blog', 'Author'),
            'update_user_id' => Yii::t('BlogModule.blog', 'Changed'),
            'publish_time'   => Yii::t('BlogModule.blog', 'Publish Time'),
            'create_time'    => Yii::t('BlogModule.blog', 'Create Time'),
            'update_time'    => Yii::t('BlogModule.blog', 'Update Time'),
            'tags'           => Yii::t('BlogModule.blog', 'Tags'),
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
        $criteria->compare('blog_id', $this->blog_id, true);
        $criteria->compare('title', $this->title, true);
        $criteria->compare('keywords', $this->keywords, true);
        $criteria->compare('description', $this->description, true);
        $criteria->compare('content', $this->content, true);
        $criteria->compare('slug', $this->slug, true);
        $criteria->compare('link', $this->link, true);
        $criteria->compare('status', $this->status);
        $criteria->compare('comment_status', $this->comment_status);
        $criteria->compare('access_type', $this->access_type);
        $criteria->compare('create_user_id', $this->create_user_id, true);
        $criteria->compare('update_user_id', $this->update_user_id, true);
        $criteria->compare('publish_time', $this->publish_time, true);
        $criteria->compare('create_time', $this->create_time, true);
        $criteria->compare('update_time', $this->update_time, true);
        $criteria->with = array('createUser', 'updateUser', 'blog');

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    public function getCommentStatus()
    {
        return $this->comment_status ? Yii::t('BlogModule.blog', 'Yes') : Yii::t('BlogModule.blog', 'No');
    }
}
