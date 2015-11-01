<?php
// TEMP LINES
echo '<pre>';
$line = '13 secs, 13:14:15 x5,4x5,3@5.0,7.0 cock and balls';

// THE CODE
// setup the variables
$output_data = array();
$multiline = 0;
$accepted_char = $accepted_chars = $format_type = array();
$current_blocks = array('W', 'T');

// pre-defined data
$units = array(
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
$all_format_types = array(
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
add_units();
$next_values_all = array(
  'R' => 'x',
  'S' => 'x',
  'P' => '@',
  'C' => '',
);
$format_follows = array(
  'T' => array('R', 'P', 'C'),
  'W' => array('R', 'P', 'C'),
  'R' => array('S', 'P', 'C'),
  'S' => array('P', 'C'),
  'P' => array('C'),
  'C' => array(''),
);
// build the initial startup data
build_next_formats ();
build_accepted_char ();
build_accepted_chars ();
$number_dump = '';
$format_dump = '';
$chunk_dump = '';
$string_array = str_split($line);
foreach($string_array as $chr)
{
  // if the character is a space just add it to the chunk and continue
  if ($chr == ' ')
  {
    $chunk_dump .= ' ';
    continue;
  }
  $format_chr = format_character($chr);
  // check character is in format and empty accepted_chars counts as allowing anything
  if (count($accepted_chars) == 0 || in_array($format_chr, $accepted_chars))
  {
    build_accepted_char ();
    build_accepted_chars ($format_chr);
    if (is_numeric($chr))
    {
      $number_dump .= $chr;
      // check the last value of the format_dump if its not 0 already set it to 0
      if (!isset($format_dump[strlen($format_dump) - 1]) || $format_dump[strlen($format_dump) - 1] != '0')
      {
        $format_dump .= '0';
      }
    }
    else
    {
      // the character is not a number so just add it to the format
      $format_dump .= $chr;
    }
    $chunk_dump .= $chr;
  }
  else
  {
    // check the previous chunk is valid
    if (format_check($format_dump))
    {
			// we are repeating the chunk
			if (count($current_blocks) == 1 && $current_blocks[0] != 'C' && $format_chr == ',')
			{
				$output_data[$current_blocks[0]][$multiline] = trim($chunk_dump);
				$multiline++;
				// reset the dumps
				$number_dump = '';
				$format_dump = '';
				$chunk_dump = '';
			}
			else
			{
				// the current chunk has finshed do something
				$output_data[$current_blocks[0]][$multiline] = trim($chunk_dump);
				// reset the dumps
				$number_dump = '';
				$format_dump = '';
				$chunk_dump = '';
				// find the options for the next format
				if (in_array($format_chr, $next_values))
				{
					// find what block comes next
					$current_blocks = array_keys ($next_values, $chr);
					// reset $format_type + next values
					build_next_formats ();
					// rebuild everything
					build_accepted_char ();
					build_accepted_chars ();
				}
				else
				{
					// assume it is a comment
					$accepted_chars = array();
					$accepted_char = array();
          $chunk_dump .= $chr;
				}
				$multiline = 0;
			}
    }
    else
    {
      flag_error('Format Error');
      break;
    }
  }
}
// add the last chunk to the data array
$output_data[$current_blocks[0]][$multiline] = $chunk_dump;

// TODO(renlok); remove
//print_r(array($number_dump,$format_dump,$chunk_dump));
print_r($output_data);
//print_r($accepted_char);

function build_next_formats ()
{
  global $all_format_types, $next_values_all, $format_follows;
  global $format_type, $next_values, $current_blocks;

  $format_type = $next_values = array();
  foreach ($current_blocks as $key)
  {
    $format_type[$key] = $all_format_types[$key];
    $next_values = array_merge($next_values, array_intersect_key($next_values_all, array_flip($format_follows[$key])));
  }
}

function build_accepted_char ()
{
  global $accepted_char, $format_type;

  $accepted_char = array();
  foreach ($format_type as $key => $sub_type)
  {
    foreach ($sub_type as $val)
    {
      if (isset($accepted_char[$key]))
      {
        $accepted_char[$key] = array_unique(array_merge($accepted_char[$key], str_split($val)));
      }
      else
      {
        $accepted_char[$key] = array_unique(str_split($val));
      }
    }
  }
}

function build_accepted_chars ($format_chr = '')
{
  global $accepted_char, $accepted_chars, $format_type, $current_blocks;
  // not an empty string then do some checks
  if ($format_chr != '')
  {
    // check all formats still valid
    $rebuild_accepted_chars = false;
    foreach ($accepted_char as $key => $val)
    {
      if(!in_array($format_chr, $val))
      {
        // remove from accepted_char
        unset($accepted_char[$key]);
        unset($format_type[$key]);
        unset($current_blocks[array_search ($key, $current_blocks)]);
        if (count($current_blocks) == 0)
        {
          $current_blocks = array('C');
        }
				// rebuild keys
        $current_blocks = array_values($current_blocks);
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
    if (is_array($accepted_char) && count($accepted_char) > 0)
    {
      $accepted_chars = array_unique(call_user_func_array('array_merge', $accepted_char));
    }
    else
    {
      $accepted_chars = array();
    }
    build_next_formats ();
  }
}

function format_check($format_dump)
{
  global $format_type;
  // the block is a comment so skip the check
  if (isset($format_type['C']))
  {
    return true;
  }
  // check if the final format_dump matches a vlid format type
  foreach ($format_type as $sub_type)
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

function add_units()
{
  global $units, $all_format_types;

  $dump_all_format_types = $all_format_types;
  foreach ($units as $type => $unit_types)
  {
    foreach ($unit_types as $unit => $val)
    {
      foreach ($dump_all_format_types[$type] as $format)
      {
        $all_format_types[$type][] = $format . $unit;
      }
    }
  }
}

function format_character($chr)
{
  $output_chr = $chr;
  if (is_numeric($chr))
  {
    $output_chr = '0';
  }
  return $output_chr;
}

function flag_error($error)
{
  echo $error;
}
?>
