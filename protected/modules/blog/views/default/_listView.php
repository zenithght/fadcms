<?php
/** @var $data Blog */
?>
<h2><?php echo CHtml::link($data->title, array('/blog/default/show/', 'slug' => $data->slug)); ?></h2>
<span class="label label-info">
    <?php echo $data->createUser->username; ?>
</span> | <i class="icon-user"></i> <?php echo $data->membersCount; ?>
<?php echo Yii::t('BlogModule.blog', 'member|members', $data->membersCount); ?> | <i class="icon-comment"></i> <?php echo $data->postsCount; ?>
<?php echo Yii::t('BlogModule.blog', 'post|posts', $data->membersCount); ?>
<hr />
