<?php
/**
 * @var $this Controller
 * @var $model Blog
 */
$this->breadcrumbs = array(
    Yii::t('BlogModule.blog', 'Blogs') => array('admin'),
    $model->title           => array('view', 'id' => $model->id),
    Yii::t('BlogModule.blog', 'Update'),
);

$this->menu = array(
    array('label' => Yii::t('BlogModule.blog', 'Blogs')),
    array('icon' => 'list-alt', 'label' => Yii::t('BlogModule.blog', 'Manage'), 'url' => array('admin')),
    array('icon' => 'file', 'label' => Yii::t('BlogModule.blog', 'Create'), 'url' => array('create')),
    array('icon'  => 'pencil',
          'label' => Yii::t('BlogModule.blog', 'Update'),
          'url'   => array('/blog/default/update', 'id' => $model->id)
    ),
    array('icon' => 'eye-open', 'label' => Yii::t('BlogModule.blog', 'View'), 'url' => array('view', 'id' => $model->id)),
);
echo $this->renderPartial('_form', array('model' => $model));
