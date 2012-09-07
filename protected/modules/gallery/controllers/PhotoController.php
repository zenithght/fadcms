<?php

class PhotoController extends Controller
{
	/**
	 * @var string the default layout for the views.
	 */
	public $layout='//layouts/main';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array('rights');
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->render('view', array(
			'model' => $this->loadModel($id),
		));
	}

	public function actionManager($id)
	{
		#$model = new Photo;
		$albums = CHtml::listData(Gallery::model()->findAll(), 'id', 'name');
		foreach ( $albums as $gid => $name )
		{
			$items[] = array('label' => $name, 'url' => $gid);
		}

		$this->render('manager', array(
			'galleryId' => $id,
			'slug' => Gallery::model()->getSlugById($id),
			'albums' => $items,
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model = new Photo;

		// $this->performAjaxValidation($model);

		if ( isset($_POST['Photo']) )
		{
			$model->attributes = $_POST['Photo'];
			if ( $model->save() )
			{
				$this->redirect(array('view', 'id'=> $model->id));
			}
		}

		$this->render('create', array(
			'model' => $model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		/** @var $model Photo */
		$model = $this->loadModel($id);
		// $this->performAjaxValidation($model);

		if ( isset($_POST['Photo']) )
		{
			$model->attributes = $_POST['Photo'];
			if ( $model->save() )
			{
				$this->redirect(array('view', 'id'=> $model->id));
			}
		}

		$this->render('update', array(
			'model' => $model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		if ( Yii::app()->request->isPostRequest )
		{
			// we only allow deletion via POST request
			$this->loadModel($id)->delete();

			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if ( !isset($_GET['ajax']) )
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
		}
		else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$dataProvider = new CActiveDataProvider('Photo');
		$this->render('index',array(
			'dataProvider' => $dataProvider,
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model = new Photo('search');
		$model->unsetAttributes();  // clear any default values
		if ( isset($_GET['Photo']) )
			$model->attributes = $_GET['Photo'];

		$this->render('admin',array(
			'model' => $model,
		));
	}


	/**
	 * Method to handle file upload thought XHR2
	 * On success returns JSON object with image info.
	 * @param $gallery_id string Gallery Id to upload images
	 * @throws CHttpException
	 */
	public function actionAjaxUpload($galleryId = null)
	{
		if ( Yii::app()->getRequest()->getIsPostRequest() )
		{
			$model = new Photo();
			$model->gallery_id = $galleryId;
			if (isset($_POST['Photo']))
				$model->attributes = $_POST['Photo'];

			$imageFile        = CUploadedFile::getInstance($model, 'image');
			$model->file_name = pathinfo($imageFile->getName(), PATHINFO_FILENAME) . '.' . $model->galleryExt;
			$model->save();

			$model->setImage($imageFile->getTempName());
			header("Content-Type: application/json");
			echo CJSON::encode(array(
				'id'          => $model->id,
				'sort'        => $model->sort,
				'name'        => (string)$model->name,//@todo: something wrong with model - it returns null, but it must return an empty string
				'description' => (string)$model->description,
				'preview'     => $model->getPreview('small'),
			));
		}
		else throw new CHttpException(403);
	}

	/**
	 * Method to update images name/description via AJAX.
	 * On success returns JSON array od objects with new image info.
	 * @throws CHttpException
	 */
	public function actionChangeData()
	{
		if (Yii::app()->getRequest()->getIsPostRequest()) {
			$data = $_POST['photo'];
			$criteria = new CDbCriteria();
			$criteria->index = 'id';
			$criteria->addInCondition('id', array_keys($data));
			/** @var $models Photo[] */
			$models = Photo::model()->findAll($criteria);

			foreach ($data as $id => $attributes)
			{
				if (isset($attributes['name']))
					$models[$id]->name = $attributes['name'];
				if (isset($attributes['description']))
					$models[$id]->description = $attributes['description'];
				$models[$id]->save();
			}
			$resp = array();
			foreach ($models as $model)
			{
				$resp[] = array(
					'id'          => $model->id,
					'sort'        => $model->sort,
					'name'        => (string)$model->name,//@todo: something wrong with model - it returns null, but it must return an empty string
					'description' => (string)$model->description,
					'preview'     => $model->getPreview(),
				);
			}
			echo CJSON::encode($resp);
		} else
			throw new CHttpException(403);
	}

	/**
	 * Saves images order according to request.
	 * Variable $_POST['order'] - new arrange of image ids, to be saved
	 * @throws CHttpException
	 */
	public function actionOrder()
	{
		if (Yii::app()->getRequest()->getIsPostRequest())
		{
			$gp = $_POST['order'];
			$orders = array();
			$i = 0;
			foreach ($gp as $k => $v)
			{
				if (!$v)
					$gp[$k] = $k;
				$orders[] = $gp[$k];
				$i++;
			}
			sort($orders);
			$i = 0;
			foreach ($gp as $k => $v)
			{
				/** @var $p Photo */
				$p = Photo::model()->findByPk($k);
				$p->sort = $orders[$i];
				$p->save(false);
				$i++;
			}
			if ($_POST['ajax'] == true)
			{
				echo CJSON::encode(array('result' => 'ok'));
			}
			else
			{
				$this->redirect($_POST['returnUrl']);
			}
		}
		else
			throw new CHttpException(403);
	}
	/**
	 * Removes image with id specified in post request.
	 * On success returns 'OK'
	 */
	public function actionAjaxDelete()
	{
		if (Yii::app()->getRequest()->getIsPostRequest())
		{
			$id = $_POST['id'];
			/** @var $photo Photo */
			$photo = Photo::model()->findByPk($id);
			if ($photo !== null && $photo->delete())
				echo 'OK';
			else
				echo 'FAIL';
		}
		else
			echo 'FAIL';
	}

	/**
	 * List images of gallery photo album @frontend
	 * @todo get only public not draft
	 */
	public function actionAlbum($slug)
	{
		$this->render('list', array('slug' => $slug, 'galleryId' => Gallery::model()->getIdBySlug($slug)));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model = Photo::model()->findByPk($id);
		if ( $model===null )
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if ( isset($_POST['ajax']) && $_POST['ajax']==='photo-form' )
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
