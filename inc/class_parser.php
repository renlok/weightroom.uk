<?php
class parser
{
  // the predefined data variables
  var $units; // array of units for each block type
  var $format_types_all; // all possible formats for all possible blocks
  var $next_values_all; // flags that show a certain type of block is coming up
  var $format_follows; // what should come after each block type
  // the working variables
  var $accepted_char;
  var $accepted_chars;
  var $format_type;
  var $current_blocks; // the blocks that are expected
  var $multiline; // for when a line contains commas we can add them as multiple sets
  var $next_values;
  // data dumps
  var $number_dump; // no current use
  var $format_dump;
  var $chunk_dump;
  // final array
  var $log_data;

  public function parser ($log_text, $bodyweight)
  {
    // build the initial startup data
    $this->construct_globals ();
    $exercise = '';
		$position = 0; // a pointer for when exercise was done
    $this->log_data = array('comment' => ''); // the output array
    // convert log_text to array
    $log_lines = explode("\n", $log_text);
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
        $position++;
				$exercise = substr($line, 1); // set exercise marker
				// add new exercise group to array
				$this->log_data[$position] = array(
						'name' => $exercise,
						'comment' => '',
						'data' => array());
				continue; // end this loop
			}

			// no exercise yet
			if ($exercise == '')
			{
				if (!empty($this->log_data['comment']))
				{
					$this->log_data['comment'] .= '<br>';
				}
				$this->log_data['comment'] .= $line;
				continue; // end this loop
			}
      else
      {
        $this->log_data[$position]['data'] = array_merge($this->log_data[$position]['data'], $this->parse_line ($line));
      }
    }
    // TODO: add a way to deal with units
  }

  public function parse_line ($line)
  {
    // build the initial startup data
    $this->current_blocks = array('W', 'T', 'C');
    $this->build_next_formats ();
    $this->build_accepted_char ();
    $this->build_accepted_chars ();
    $this->number_dump = '';
    $this->format_dump = '';
    $this->chunk_dump = '';
    $this->multiline = 0;
    $output_data = array();
    $string_array = str_split($line);
    foreach($string_array as $chr)
    {
      // if the character is a space just add it to the chunk and continue
      if ($chr == ' ')
      {
        $this->chunk_dump .= ' ';
        continue;
      }
      $format_chr = $this->format_character($chr);
      // check character is in format and empty accepted_chars counts as allowing anything
      if (count($this->accepted_chars) == 0 || in_array($format_chr, $this->accepted_chars))
      {
        $this->build_accepted_char ();
        $this->build_accepted_chars ($format_chr);
        if (is_numeric($chr))
        {
          $this->number_dump .= $chr;
          // check the last value of the format_dump if its not 0 already set it to 0
          if (!isset($this->format_dump[strlen($this->format_dump) - 1]) || $this->format_dump[strlen($this->format_dump) - 1] != '0')
          {
            $this->format_dump .= '0';
          }
        }
        else
        {
          // the character is not a number so just add it to the format
          $this->format_dump .= $chr;
        }
        $this->chunk_dump .= $chr;
      }
      else
      {
        // check the previous chunk is valid
        if ($this->format_check($this->format_dump))
        {
    			// we are repeating the chunk
    			if (count($this->current_blocks) == 1 && $this->current_blocks[0] != 'C' && $format_chr == ',')
    			{
    				$output_data[$this->multiline][$this->current_blocks[0]] = trim($this->chunk_dump);
    				$this->multiline++;
    				// reset the dumps
    				$this->number_dump = '';
    				$this->format_dump = '';
    				$this->chunk_dump = '';
    			}
    			else
    			{
    				// the current chunk has finshed do something
    				$output_data[$this->multiline][$this->current_blocks[0]] = trim($this->chunk_dump);
    				// reset the dumps
    				$this->number_dump = '';
    				$this->format_dump = '';
    				$this->chunk_dump = '';
    				// find the options for the next format
    				if (in_array($format_chr, $this->next_values))
    				{
    					// find what block comes next
    					$this->current_blocks = array_keys ($this->next_values, $chr);
    					// reset $format_type + next values
    					$this->build_next_formats ();
    					// rebuild everything
    					$this->build_accepted_char ();
    					$this->build_accepted_chars ();
    				}
    				else
    				{
    					// assume it is a comment
    					$this->accepted_chars = array();
    					$this->accepted_char = array();
              $this->chunk_dump .= $chr;
    				}
    				$this->multiline = 0;
    			}
        }
        else
        {
          $this->flag_error('Format Error');
          break;
        }
      }
    }
    // add the last chunk to the data array
    $output_data[$this->multiline][$this->current_blocks[0]] .= $this->chunk_dump;

    return $output_data;
  }

  private function construct_globals ()
  {
    // pre-defined data
    $this->units = array(
      'T' => array(
              's' => 's',
              'secs' => 's',
              'sec' => 's',
              'seconds' => 's',
              'second' => 's',
              'm' => 'm',
              'mins' => 'm',
              'min' => 'm',
              'minutes' => 'm',
              'minute' => 'm',
              'h' => 'h',
              'hrs' => 'h',
              'hr' => 'h',
              'hours' => 'h',
              'hour' => 'h',
            ),
      'W' => array(
              'kg' => 'kg',
              'kgs' => 'kg',
              'lb' => 'lb',
              'lbs' => 'lb',
            ),
    );
    $this->format_types_all = array(
      'T' => array(
              ('0:0:0'),
              ('0:0'),
              ('0.0'),
              ('0'),
            ),
      'W' => array(
              ('0.0'),
              ('0'),
              ('BW'),
              ('BW+0.0'),
              ('BW+0'),
              ('BW-0.0'),
              ('BW-0'),
            ),
      'R' => array(('0')),
      'S' => array(('0')),
      'P' => array(
              ('0.0'),
              ('0'),
            ),
      'C' => array(('')),
    );
    // add units to the formats
    $this->add_units();
    $this->next_values_all = array(
      'R' => 'x',
      'S' => 'x',
      'P' => '@',
      'C' => '',
    );
    $this->format_follows = array(
      'T' => array('R', 'P', 'C'),
      'W' => array('R', 'P', 'C'),
      'R' => array('S', 'P', 'C'),
      'S' => array('P', 'C'),
      'P' => array('C'),
      'C' => array(''),
    );
  }

  private function build_next_formats ()
  {
    $this->format_type = array();
    $this->next_values = array();
    foreach ($this->current_blocks as $key)
    {
      $this->format_type[$key] = $this->format_types_all[$key];
      $this->next_values = array_merge($this->next_values, array_intersect_key($this->next_values_all, array_flip($this->format_follows[$key])));
    }
  }

  private function build_accepted_char ()
  {
    $this->accepted_char = array();
    foreach ($this->format_type as $key => $sub_type)
    {
      foreach ($sub_type as $val)
      {
        if (isset($this->accepted_char[$key]))
        {
          $this->accepted_char[$key] = array_unique(array_merge($this->accepted_char[$key], str_split($val)));
        }
        else
        {
          $this->accepted_char[$key] = array_unique(str_split($val));
        }
      }
    }
  }

  private function build_accepted_chars ($format_chr = '')
  {
    // not an empty string then do some checks
    if ($format_chr != '')
    {
      // check all formats still valid
      $rebuild_accepted_chars = false;
      foreach ($this->accepted_char as $key => $val)
      {
        if(!in_array($format_chr, $val))
        {
          // remove from accepted_char
          unset($this->accepted_char[$key]);
          unset($this->format_type[$key]);
          unset($this->current_blocks[array_search ($key, $this->current_blocks)]);
          if (count($this->current_blocks) == 0)
          {
            $this->current_blocks = array('C');
          }
  				// rebuild keys
          $this->current_blocks = array_values($this->current_blocks);
          $rebuild_accepted_chars = true;
        }
      }
    }
    else
    {
      $rebuild_accepted_chars = true;
    }
    // if some formats have become invalid rebuild the master format array
    if ($rebuild_accepted_chars)
    {
      if (is_array($this->accepted_char) && count($this->accepted_char) > 0)
      {
        $this->accepted_chars = array_unique(call_user_func_array('array_merge', $this->accepted_char));
      }
      else
      {
        $this->accepted_chars = array();
      }
      $this->build_next_formats ();
    }
  }

  private function format_check($format_dump)
  {
    // the block is a comment so skip the check
    if (isset($this->format_type['C']))
    {
      return true;
    }
    // check if the final format_dump matches a vlid format type
    foreach ($this->format_type as $sub_type)
    {
      foreach ($sub_type as $key => $format_string)
      {
        //foreach ($val as $format_string)
        {
          if ($format_string == $format_dump)
          {
            return true;
          }
        }
      }
    }
    return false;
  }

  private function add_units()
  {
    global $units, $format_types_all;

    $dump_all_format_types = $this->format_types_all;
    foreach ($this->units as $type => $unit_types)
    {
      foreach ($unit_types as $unit => $val)
      {
        foreach ($dump_all_format_types[$type] as $format)
        {
          $this->format_types_all[$type][] = $format . $unit;
        }
      }
    }
  }

  // TODO: add all the possible characters that will be accpected
  private function format_character($chr)
  {
    $output_chr = $chr;
    if (is_numeric($chr))
    {
      $output_chr = '0';
    }
    return $output_chr;
  }

  private function flag_error($error)
  {
    echo $error;
  }
}
?>
