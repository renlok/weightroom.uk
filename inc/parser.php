http://www.regexr.com/
http://www.phpliveregex.com/
<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$log = "This    is  how a   log looks   like    when    you are in  edit    mode,   it's    just    text.   You can also    type    links,  like    this:   http://weightxreps.net  or  http://www.youtube.com/watch?v=dnkQRRrsCuo  (youtube    links   are highlighted in  blue)

When    you want    to  log an  exercise,   you type    it's    name,   and it  will    be  created (if it  doesn't exists) and added   to  your    own personal    list    of  exercises.  

Start   every   exercise    name    with    a   hash    symbol  \"#\" so  the system  can understand  that    you are logging an  exercise.

#Bench  Press
140 if  you put just    a   number, 1   rep and 1   set will    be  assumed.
100 x   1   x   3   Classic format, weight  x   reps    x   sets.
225lb   x   1   you can use KG  or  LB  if  you want.
80kg    x   5,5,5   Optional    format, type    the reps    of  each    set separated   with    a   comma.

Now i   will    show    you how to  log bodyweight  exercises,  remember    to  log how much    you weight  if  you want    to  use this    feature.

#Pushups
BW  x   50  Here    i   used    my  bodyweight  (75kg)  but i   typed   BW.
BW+10kg x   3   x   3   Here    i   added   a   10kg    plate   to  my  back.
BW-5kg  Here    i   added   bands,  to  make    it  easyer  for me...
BW  x   10,8,5  same    as  above,  weight  x   rep,    rep,    rep

If  you make    a   mistake,    it  will    be  highlighted in  red,    like    this:

#some   exercise
12and   the error...    because the system  was especting   weight  x   reps.

Everything  below   the error   will    be  ignored untill  you correct it.";

$log_data = array();
$bodyweight = 88;
$units = 1; // 1 = kg, 2 = lb
$log_data['comment'] = '';
$log_lines = explode("\n", $log);

$exercise = '';
foreach ($log_lines as $line)
{
	// check if new exercise
	if ($line[0] == '#')
	{
		$exercise = substr($line, 1); // set exercise marker
		if (!isset($log_data[$exercise]))
			$log_data[$exercise] = array('name' => $exercise,
										'comment' => '',
										'sets' => array()); // create entry to array
		
		continue; // end this loop
	}
	
	// no exercise yet
	if ($exercise == '')
	{
		$log_data['comment'] .= '<br>' . $line;
		continue; // end this loop
	}

	// set details of excersice
	if (is_numeric($line[0])) // using weight
	{
		if (preg_match("/^([0-9]+)\s*(lb|kg)*/", $line, $matches)) // 1 = weight, 2 = lb/kg
		{
			// clear the weight from the line
			$line = str_replace($matches[0], '', $line);
			// check if units were used
			if (isset($matches[2]))
				$weight = correct_weight($matches[1], $matches[2], $units);
			else
				$weight = $matches[1];
			// add the data to the array
			$log_data[$exercise]['sets'][] = get_reps($exercise, $weight, $line);
		}
	}
	elseif ($line[0] == 'B' && $line[1] == 'W') // using bodyweight
	{
		if (preg_match("/^BW(\+|-)*\s*([0-9]+)*\s*(kg|lb)*/", $line, $matches)) // 1= +/- 2= weight, 3= lb/kg
		{
			// clear the weight from the line
			$line = str_replace($matches[0], '', $line);
			// check if units were used
			if ($matches[0] == 'BW')
			{
				$weight = $bodyweight;
			}
			else
			{
				if (isset($matches[3]))
					$correct_weight = correct_weight($matches[2], $matches[3], $units);
				else
					$correct_weight = $matches[2];
				// was it + or - BW
				if ($matches[1] == '+')
					$weight = $bodyweight + $correct_weight;
				else
					$weight = $bodyweight - $correct_weight;
			}
			// add the data to the array
			$log_data[$exercise]['sets'][] = get_reps($exercise, $weight, $line);
		}
	}
	else
	{
		$log_data[$exercise]['comment'] .= '<br>' . $line;
	}
	// /^BW(\+|-)*\s*([0-9]+)*\s*(kg|lb)*/g // matches BW+10kg
	// /^([0-9]+)\s*(lb|kg)*/g      matches weight given
} 

function correct_weight($weight, $unit_used, $unit_want) // $unit_used = kg/lb $unit_want = 1/2
{
	if (($unit_used == 'kg' && $unit_want == 1) || ($unit_used == 'lb' && $unit_want == 2))
	{
		return $weight;
	}
	elseif ($unit_used == 'kg' && $unit_want == 2)
	{
		return ($weight * 2.20462); // convert to lb
	}
	elseif ($unit_used == 'lb' && $unit_want == 1)
	{
		return ($weight * 0.453592); // convert to kg
	}
	else
	{
		return $weight;
	}
}

// :(
function get_reps($exercise, $weight, $line)
{
	// set the vaariables
	$reps = '';
	$sets = '';
	$reps_given = false;
	$waiting_for_reps = false;
	$sets_given = false;
	$waiting_for_sets = false;
	$sets_array = array();
	$cleanline = str_replace(' ', '', $line);
	$spacescount = 0;
	$lettercount = 0;
	$string_array = str_split($line);
	foreach ($string_array as $chr)
	{
		$lettercount++;
		// skip blank spaces
		if ($chr == ' ')
		{
			$spacescount++;
			continue;
		}
		// end of sets just add the comment
		if (!is_numeric($chr) && !($chr == 'x' || $chr == 'X' || $chr == ','))
		{
			break;
		}
		// x detected and no reps have been set yet, set it to wait for reps input
		if (($chr == 'x' || $chr == 'X') && !$reps_given && !$waiting_for_reps)
		{
			$waiting_for_reps = true;
			continue;
		}
		// add reps
		if (is_numeric($chr) && !$reps_given && $waiting_for_reps)
		{
			$reps .= $chr;
			$chrnum = $lettercount - $spacescount;
			$nextchar = substr($cleanline, $chrnum, 1);
			if (!is_numeric($nextchar))
			{
				$reps_given = true;
			}
			continue;
		}
		// x detected, reps have been set but no sets have been, set it to wait for reps input
		if (($chr == 'x' || $chr == 'X') && $reps_given && !$sets_given && !$waiting_for_sets)
		{
			$waiting_for_sets = true;
			continue;
		}
		// add sets
		if (is_numeric($chr) && !$sets_given && $waiting_for_sets)
		{
			$sets .= $chr;
			$chrnum = $lettercount - $spacescount;
			$nextchar = substr($cleanline, $chrnum, 1);
			if (!is_numeric($nextchar))
			{
				$sets_given = true;
			}
			continue;
		}
		// comma format
		if ($chr == ',' && $reps_given)
		{
			// check theres a number after
			$chrnum = $lettercount - $spacescount;
			$nextchar = substr($cleanline, $chrnum, 1);
			if (is_numeric($nextchar))
			{
				$reps .= $chr;
				$reps_given = false;
			}
		}
	}
	$line = substr($line, ($lettercount - 1));
	return array('weight' => $weight,
				'reps' => ($reps == '') ? 0 : $reps,
				'sets' => ($sets == '') ? 0 : $sets,
				'line' => $line,);
}

print_r($log_data);
?>