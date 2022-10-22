<?php

	namespace umono\multiple\helpers;

	use app\common\tools\Ali\AliOss;
	use yii\helpers\FileHelper;
	use yii\web\ForbiddenHttpException;
	use yii\web\UploadedFile;

	class UploadHelper
	{
		private $userId;

		private $watermark = false;             // 水印
		private $watermarkText = 'watermark';   // 水印文字
		private $watermarkSize = [100, 100];    // 水印文字尺寸


		private $fileHandleType;        // 文件类型名
		private $fileData;              // 文件数据
		private $fileByName = 'file';   // 文件名称
		private $basePath;              // 存储的路径
		private $publicPath;            // 资源路径
		public $param;                  // 传递的所有参数

		public $ifUploadOss = true;
		public $ifUpload = "ali";      // ali,qin

		// 上传文件的表单属性 如果为base64则视为base64资源，其他做为文件参数属性.参考 UploadedFile::getInstancesByName
		public $canTypes = ['photo', 'base64', 'video'];


		/**
		 * 设置文件名称
		 *
		 * @param $name
		 * @return void
		 */
		public function setFileByName($name)
		{
			$this->fileByName = $name;
		}


		/**
		 * @throws \yii\base\Exception
		 * @throws ForbiddenHttpException
		 */
		public function __construct($type, $param, $userId = 0)
		{
			$this->fileHandleType = $type;
			$this->userId         = $userId;
			$this->param          = $param;

			if (!in_array($type, $this->canTypes)) {
				$this->error('Unsupported upload type name.');
			}
			$publicPath = "/uploads/admins/" . $this->fileHandleType . '/' . date('Y-m-d') . '/';

			$this->basePath   = dirname(\Yii::getAlias("@app/web")) . $publicPath;
			$this->publicPath = $publicPath;

			if (!file_exists($this->basePath)) {
				if (!FileHelper::createDirectory($this->basePath, 0777)) {
					$this->error("Failed to create folder.");
				}
			}
		}


		/**
		 * @return void
		 * @throws ForbiddenHttpException
		 */
		private function getFileResources()
		{
			switch ($this->fileHandleType) {
				case 'base64':
					$this->fileData = $this->getBase64File();
					break;
				default:
					$this->fileData = UploadedFile::getInstancesByName($this->fileByName)[0];
					break;
			}
		}


		/**
		 * 处理图片上传且返回对应数据
		 *
		 * @return array
		 * @throws ForbiddenHttpException
		 */
		public function handler(): array
		{
			$file = $this->fileData;
			if (!$file) {
				$this->getFileResources();
				$file = $this->fileData;
			}
			$resetName = md5(time() . uniqid()) . '.' . $file->extension;

			$filePath = $this->basePath . $resetName;

			if ($this->saveAs($file, $filePath)) {
				$publicFilePath = $this->publicPath . $resetName;

				// 图片资源处理
				$type = exif_imagetype($filePath);

				if($type > 0 || $type < 19) {
					// 压缩图片
					ImageHelper::actionThumb($filePath, $this->watermarkText, $this->watermarkSize);

					// 是否存储水印
					if ($this->watermark) {
						ImageHelper::actionText($filePath, $this->watermarkText, $this->watermarkSize);
					}
				}

				$url = $_ENV["APP_URL"] . $publicFilePath;
				if ($this->ifUploadOss) {

					switch ($this->ifUpload) {
						case 'ali':
							$url = AliOss::uploadFile($resetName, $file->extension, $filePath);
							break;
						case "qin":
							$url = 'qiniu??';
							break;
						default:
							break;
					}

				}
				$result = [
					'title'          => $resetName,
					'url'            => $url,
					'publicFilePath' => $publicFilePath,
					'user_id'        => $this->userId,
				];
			} else {
				$this->error('file write failed:(');
			}

			return $result;
		}

		// 保存图片
		private function saveAs($file, $filePath)
		{
			switch ($this->fileHandleType) {
				case 'base64':
					return file_put_contents(
						$filePath,
						base64_decode(
							str_replace(
								$file->search,
								'',
								$file->file
							)
						)
					);
				default:
					return $file->saveAs($filePath);
			}
		}


		/**
		 * 处理base64文件 仅仅支持图片格式
		 *
		 * @return \stdClass
		 * @throws ForbiddenHttpException
		 */
		private function getBase64File(): \stdClass
		{
			$base64_image_content = $this->param[$this->fileByName] ?? '';
			preg_match(
				'/^(data:\s*image\/(\w+);base64,)/',
				$base64_image_content,
				$result
			);
			if (empty($result)) {
				$this->error('fail to read file.');
			}
			$obj            = new \stdClass();
			$obj->search    = $result[1];
			$obj->extension = $result[2];
			$obj->file      = $base64_image_content;

			return $obj;
		}


		/**
		 * @throws ForbiddenHttpException
		 */
		private function error($msg)
		{
			throw new ForbiddenHttpException($msg);
		}
	}