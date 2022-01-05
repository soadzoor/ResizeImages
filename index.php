<?php
	header('Content-Type: text/html; charset=utf-8');
	ini_set('memory_limit', '-1');
	ob_implicit_flush(true);
	

	$desiredWidth = 6080;
	$desiredFormat = 'jpg';
	
	$crop = false;
	
	function resize_image($file, $desiredWidth, $withAlpha = false)
	{
		list($width, $height) = getimagesize($file);
		
		$ratio = $width / $height;
		
		$w = min($desiredWidth, $width);
		$h = round($w / $ratio);
		
		if (strpos($file, '.png'))
		{
			$src = imagecreatefrompng($file);
		}
		else if (strpos($file, '.jpg'))
		{
			$src = imagecreatefromjpeg($file);
		}
		else
		{
			echo "Format not supported.: ".$file;
			return;
		}
		
		$dst = imagecreatetruecolor($w, $h);
		
		if ($withAlpha)
		{
			imagealphablending($dst, false);
			imagesavealpha($dst, true);
			$transparent = imagecolorallocatealpha($dst, 255, 255, 255, 255);
			imagefilledrectangle($dst, 0, 0, $w, $h, $transparent);
		}
		
		imagecopyresampled($dst, $src, 0, 0, 0, 0, $w, $h, $width, $height);
		
		$cropDX = 6;
		$cropDY = 4;
		$cropWidth = $w - 2*$cropDX;
		$cropHeight = $h - $cropDY - 27;
		
		if ($GLOBALS['crop'])
		{
			$cropDX = 6;
			$cropDY = 4;
			$cropWidth = $w - 2*$cropDX;
			$cropHeight = $h - $cropDY - 27;
			
			$dst = imagecrop($dst, ['x' => $cropDX, 'y' => $cropDY, 'width' => $cropWidth, 'height' => $cropHeight]);
		}

		return $dst;
	}
	
	function savePngAsJpg($pngPath, $jpgPath)
	{
		$image = resize_image($pngPath, $GLOBALS['desiredWidth']);
		$bg = imagecreatetruecolor(imagesx($image), imagesy($image));
		imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
		imagealphablending($bg, TRUE);
		imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
		imagedestroy($image);
		$quality = 75; // 0 = worst / smaller file, 100 = better / bigger file 
		imagejpeg($bg, $jpgPath, $quality);
		imagedestroy($bg);
	}
	
	function savePngAsPng($oldPath, $newPath)
	{
		$image = resize_image($oldPath, $GLOBALS['desiredWidth'], true);
		$quality = 9; // 0 = no compression / larger file, 9 = max compression / smaller file 
		imagepng($image, $newPath, $quality);
		imagedestroy($image);
	}
	
	$images = scandir('input'); /** [0] is ., [1] is .. */
	
	$len = count($images);
	for ($i = 2; $i < $len; ++$i)
	{
		$originalPath = 'input/'.$images[$i];
		$newFileName = str_replace('png', 'jpg', $images[$i]);
		$newPath = $GLOBALS['desiredFormat'] == 'png' ? 'output/'.$images[$i] : 'output/'.$newFileName;
		
		if ($desiredFormat == 'png')
		{
			savePngAsPng($originalPath, $newPath);
		}
		else
		{
			savePngAsJpg($originalPath, $newPath);
		}
		
		ob_start();
		echo ($i - 1).' / '.($len-2).' done!<br>';
		ob_end_flush();
		ob_flush();
		flush();
	}
	
	echo "Finished.";
?>