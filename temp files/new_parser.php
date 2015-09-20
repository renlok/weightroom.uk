<?php
// TEMP LINES
$data = array();
$format = array('0:0:0', '0.0');
$line = '13:23:56 cock and balls';
$accepted_char = $accepted_chars = $format_type = array();

// THE CODE
$format_type = array(
  'T' => array('0:0:0'),
  'W' => array('0.0'),
);
$next_values = array(
  'R' => 'x',
  'P' => '@',
  'C' => '',
);
$all_format_types = array(
  'T' => array('0:0:0'),
  'W' => array('0.0'),
  'R' => array('0'),
  'S' => array('0'),
  'P' => array('0.0'),
);
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
  $format_chr = (is_numeric($chr)) ? '0' : $chr;
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
  }
  else
  {
    // the current chunk has finshed do something
    $data[] = trim($chunk_dump);
    // reset the dumps
    $number_dump = '';
    $format_dump = '';
    $chunk_dump = '';
    // find the options for the next format
    if (in_array($format_chr, $next_values))
    {
      $next_formats = array_keys ($next_values, $chr);
      // reset $format_type
      $format_type = array();
      foreach ($next_formats as $key)
      {
        $format_type[$key] = $all_format_types[$key];
      }
      // rebuild everything
      build_accepted_char ();
      build_accepted_chars ();
    }
    else
    {
      // assume it is a comment
      $accepted_chars = array();
      $accepted_char = array();
    }
  }
  $chunk_dump .= $chr;
}
// add the last chunk to the data array
$data[] = $chunk_dump;
print_r(array($number_dump,
$format_dump,
$chunk_dump));
print_r($data);
print_r($accepted_char);

function build_accepted_char ()
{
  global $accepted_char, $format_type;

  foreach ($format_type as $key => $val)
  {
    $accepted_char[$key] = array_unique(str_split(implode('', $val)));
  }
}

function build_accepted_chars ($format_chr = '')
{
  global $accepted_char, $accepted_chars, $format_type;
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
  }
}

?>
