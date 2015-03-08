<?php
function LoadPNG($imgname)
{
    /* Attempt to open */
    $im = @imagecreatefrompng('../img/barbell.png');
	$white = imagecolorallocate($im, 255, 255, 255);
	$red = imagecolorallocate($im, 255, 255, 255);
	$green = imagecolorallocate($im, 255, 255, 255);
	$blue = imagecolorallocate($im, 255, 255, 255);
	$yellow = imagecolorallocate($im, 255, 255, 255);

    /* See if it failed */
    if(!$im)
    {
        /* Create a blank image */
        $im  = imagecreatetruecolor(150, 30);
        $bgc = imagecolorallocate($im, 255, 255, 255);
        $tc  = imagecolorallocate($im, 0, 0, 0);

        imagefilledrectangle($im, 0, 0, 150, 30, $bgc);

        /* Output an error message */
        imagestring($im, 1, 5, 5, 'Error loading ' . $imgname, $tc);
    }

    return $im;
}

function find_plate($plates, $weight, $pos)
{
	if ($weight >= 25)
	{
		$plates[] = 25;
		$weight = $weight - 25;
	}
	elseif ($weight >= 20)
	{
		$plates[] = 20;
		$weight = $weight - 20;
	}
	elseif ($weight >= 15)
	{
		$plates[] = 15;
		$weight = $weight - 15;
	}
	elseif ($weight >= 10)
	{
		$plates[] = 10;
		$weight = $weight - 10;
	}
	elseif ($weight >= 5)
	{
		$plates[] = 5;
		$weight = $weight - 5;
	}
	elseif ($weight >= 2.5)
	{
		$plates[] = 2.5;
		$weight = $weight - 2.5;
	}
	elseif ($weight >= 2)
	{
		$plates[] = 2;
		$weight = $weight - 2;
	}
	elseif ($weight >= 1.5)
	{
		$plates[] = 1.5;
		$weight = $weight - 1.5;
	}
	elseif ($weight >= 1)
	{
		$plates[] = 1;
		$weight = $weight - 1;
	}
	elseif ($weight >= 0.5)
	{
		$plates[] = 0.5;
		$weight = $weight - 0.5;
	}
	if ($weight >= 0.5)
		$plates = find_plate($plates, $weight, $pos);
	return $plates;
}

$weight = 160;
print_r(find_plate(array(), ($weight-20)/2, 0));
//header('Content-Type: image/png');

$img = LoadPNG('bogus.image');

imagepng($img);
imagedestroy($img);
?>