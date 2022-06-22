<?php

	namespace umono\multiple\helpers;

	use app\common\helpers\OverHelper;
	use Imagine\Image\ManipulatorInterface;
	use yii\imagine\Image;

	class ImageHelper
	{
		//裁剪
		public static function actionCrop($file, $width, $height)
		{
			Image::crop($file, $width, $height, [500, 500])
				->save($file);
		}

		//旋转
		public static function actionRotate($file)
		{
			Image::frame($file, 5, '666', 1)
				->rotate(-45)
				->save($file, ['quality' => 50]);
		}

		//缩略图（压缩）
		public static function actionThumb($file, $width, $height)
		{
			$imge        = Image::thumbnail($file, $width, $height, ManipulatorInterface::THUMBNAIL_FLAG_NOCLONE);
			$fileName    = basename($file);
			$fileNameArr = explode(".", $fileName);
			$thumbFile   = $file . '_ss_.' . $fileNameArr[1];
			$imge->save($thumbFile);
			return $thumbFile;
		}

		//图片水印
		public static function actionWatermark($file, $watermarkImg = '', $coordinate = [10, 10])
		{
			return Image::watermark($file, $watermarkImg, $coordinate)
				->save($file);
		}


		//文字水印
		//字体参数 the file path or path alias (string)
		public static function actionText(
			$file,
			$text = 'Yaoyao',
			$coordinate = [],
			$option = ['size' => 40, 'color' => '848484']
		)
		{
			if (empty($coordinate)) {
				$w          = \app\common\helpers\ImageHelper::text_width($text, $option['size'] ?? 14);
				$h          = ImageHelper::text_height($text, $option['size'] ?? 14);
				$fileInfo   = getimagesize($file);
				$coordinate = [$fileInfo[0] - ($w + 20), $fileInfo[1] - (30 + $h)];
				unset($fileInfo);
				unset($w);
				unset($h);
			}
			$coordinate1 = [$coordinate[0] + 2, $coordinate[1] + 2];

			$font = \Yii::getAlias('@app/web') . '/font/msyh.ttf';
			$img  = Image::text($file, $text, $font, $coordinate1, $option)
				->save($file);
			unset($img);
			unset($font);
		}

		public static function isImage($filename)
		{
			$types = '.gif|.jpeg|.png|.bmp'; //定义检查的图片类型
			if (file_exists($filename)) {
				if (($info = @getimagesize($filename))) {
					return 0;
				}

				$ext = image_type_to_extension($info['2']);
				return stripos($types, $ext);
			} else {
				return false;
			}
		}

		public static function get_bbox($text, $size, $angle = 0)
		{
			$font = \Yii::getAlias('@app/web') . '/font/msyh.ttf';
			return imagettfbbox($size, $angle, $font, $text);
		}

		public static function text_height($text, $size)
		{
			$box    = self::get_bbox($text, $size);
			$height = $box[3] - $box[5];
			return $height;
		}

		public static function text_width($text, $size)
		{
			$box   = self::get_bbox($text, $size);
			$width = $box[4] - $box[6];
			return $width;
		}


		// 圆角
		public static function radiusImage($imgPath, $radius = 0, $out = false, $outFile = '')
		{
			OverHelper::veryLong();
			$ext    = getimagesize($imgPath);
			$srcImg = null;
			switch ($ext['mime']) {
				case 'image/jpeg':
					$srcImg = imagecreatefromjpeg($imgPath);
					break;
				case 'image/png':
					$srcImg = imagecreatefrompng($imgPath);
					break;
				case 'image/gif':
					$srcImg = imagecreatefromgif($imgPath);
					break;
			}
			$info   = getimagesize($imgPath);
			$w      = $info[0];
			$h      = $info[1];
			$radius = $radius == 0 ? (min($w, $h) / 2) : $radius;
			$img    = imagecreatetruecolor($w, $h);
			imagesavealpha($img, true);
			$bg = imagecolorallocatealpha($img, 0, 0, 0, 127);
			imagefill($img, 0, 0, $bg);
			$r = $radius;
			if ($radius > 0) {
				for ($x = 0; $x < $w; $x++) {
					for ($y = 0; $y < $h; $y++) {
						$rgbColor = imagecolorat($srcImg, $x, $y);
						if (($x >= $radius && $x <= ($w - $radius)) || ($y >= $radius && $y <= ($h - $radius))) {
							//不在四角的范围内,直接画
							imagesetpixel($img, $x, $y, $rgbColor);
						} else {
							//上左
							$y_x = $r; //圆心X坐标
							$y_y = $r; //圆心Y坐标
							if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
								imagesetpixel($img, $x, $y, $rgbColor);
							}
							//上右
							$y_x = $w - $r; //圆心X坐标
							$y_y = $r; //圆心Y坐标
							if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
								imagesetpixel($img, $x, $y, $rgbColor);
							}
							//下左
							$y_x = $r; //圆心X坐标
							$y_y = $h - $r; //圆心Y坐标
							if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
								imagesetpixel($img, $x, $y, $rgbColor);
							}
							//下右
							$y_x = $w - $r; //圆心X坐标
							$y_y = $h - $r; //圆心Y坐标
							if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
								imagesetpixel($img, $x, $y, $rgbColor);
							}
						}
					}
				}
			}
			if ($out === true) {
				imagepng($img, $outFile);
				imagedestroy($img);
				return $outFile;
			} else {
				imagepng($img);
				imagedestroy($img);
			}
		}
	}