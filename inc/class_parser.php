<?php
/*
loop
{

}
*/
class parser
{
  var $logText;
  var $logTextCompressed;
  var $logTextPointer;
  var $logTextCompressedPointer;
  var $dump;
  var $next;

  function something()
  {
    // set up the variables we need
    // where the parsed data will be stored
		$log_data = array();
		$log_data['comment'] = ''; // pre set this
		$log_lines = explode("\n", $log);

		$exercise = '';
		$position = 0; // a pointer for when each set was done (to keep the order)
		$exersiceposition = 0; // a second position pointer
		$exersicepointers = array(); // for if there is multiple groups of exercise sets
    // loop through each line
    foreach ($log_lines as $line)
		{
      // check if blank line
			if (strlen($line) == 0)
			{
				continue;
			}

			// check if new exercise
			if ($line[0] == '#')
			{
				$exercise = substr($line, 1); // set exercise marker
				if (isset($exersicepointers[$exercise]))
					$exersicepointers[$exercise]++;
				else
					$exersicepointers[$exercise] = 0;
				// add new exercise group to array
				$log_data[$exercise][$exersicepointers[$exercise]] = array(
						'name' => $exercise,
						'comment' => '',
						'position' => $exersiceposition,
						'sets' => array());
				$exersiceposition++;
				continue; // end this loop
			}

			// no exercise yet
			if ($exercise == '')
			{
				if (!empty($log_data['comment']))
				{
					$log_data['comment'] .= '<br>';
				}
				$log_data['comment'] .= $line;
				continue; // end this loop
			}

      // we now loop through each character in the line
      $string_array = str_split($line);
      $this->next = array('W', 'T'); //could be generated automatically
      $this->dump = '';
      foreach ($string_array as $chr)
      {

      }
    }
  }

  function buildFormatArray()
  {
    $this->formats = array();
    foreach($this->next as $next)
    {
      $this->formats[$next] = $this->lexus[$next]['format'];
    }
  }

  function charExpected()
  {
    $this->expected = array();
    foreach ($this->formats as $format_type)
    {
      foreach ($format_type as $format)
      {
        $this->expected[] = $format[0];
        // if current format is number but a number has already been added allow to go to next character
        if ($this->dump[strlen($this->dump) - 1]
      }
    }
  }

  function defineLexus()
  {
    // weight
    $this->lexus['W'] = array(
      'next' => array('R', 'S', 'RPE', 'C'),
      'format' => array('0', '0.0', 'BW+0', 'BW-0'),
      'comma' => true,
      'units' => true,
      'preceeded' => array()
    );
    // time
    $this->lexus['T'] = array(
      'next' => array('R', 'S', 'RPE', 'C'),
      'format' => array('0', '0:0', '0:0:0'),
      'comma' => true,
      'preceeded' => array()
    );
    // reps
    $this->lexus['R'] = array(
      'next' => array('S', 'RPE', 'C'),
      'format' => array('0'),
      'comma' => true,
      'preceeded' => array('x', 'X')
    );
    // sets
    $this->lexus['S'] = array(
      'next' => array('RPE', 'C'),
      'format' => array('0'),
      'preceeded' => array('x', 'X')
    );
    // rpe
    $this->lexus['RPE'] = array(
      'next' => array('C'),
      'format' => array('0', '0.0'),
      'comma' => true,
      'preceeded' => array('@')
    );
    // comment
    $this->lexus['C'] = array(
      'next' => array(),
      'format' => array(),
      'preceeded' => '',
      'end' => true
    );
  }
}
?>
