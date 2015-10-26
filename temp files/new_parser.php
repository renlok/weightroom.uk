<?php
// TEMP LINES
$data = array();
$line = '13:23:56, 13:14:15 x5x5@5.0 cock and balls';
$accepted_char = $accepted_chars = $format_type = array();
$current_blocks = array('W', 'T');

// THE CODE
$format_type = array(
  'T' => array('0:0:0'),
  'W' => array('0.0'),
);
// TODO better way to deal with commas
$next_values = array(
  'W' => ',',
  'T' => ',',
  'R' => 'x',
  'P' => '@',
  'C' => '',
);
// TODO: allow this to work wth multiple format types for each
$all_format_types = array(
  'T' => array('0:0:0'),
  'W' => array('0.0'),
  'R' => array('0'),
  'S' => array('0'),
  'P' => array('0.0'),
  'C' => array(''),
);
$next_values_all = array(
  'W' => ',',
  'T' => ',',
  'R' => 'x',
  'S' => 'x',
  'P' => '@',
  'C' => '',
);
$format_follows = array(
  'T' => array('T', 'R', 'P', 'C'),
  'W' => array('W', 'R', 'P', 'C'),
  'R' => array('S', 'P', 'C'),
  'S' => array('P', 'C'),
  'P' => array('C'),
  'C' => array(''),
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
    // check the previous chunk is valid
    if (format_check($format_dump))
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
      }
    }
    else
    {
      flag_error('Format Error');
      break;
    }
  }
  $chunk_dump .= $chr;
}
// add the last chunk to the data array
$data[] = $chunk_dump;

// TODO(renlok); remove
print_r(array($number_dump,
$format_dump,
$chunk_dump));
print_r($data);
print_r($accepted_char);

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

  foreach ($format_type as $key => $val)
  {
    $accepted_char[$key] = array_unique(str_split(implode('', $val)));
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
  foreach ($format_type as $key => $val)
  {
    foreach ($val as $format_string)
    {
      if ($format_string == $format_dump)
      {
        return true;
      }
    }
  }
  return false;
}

function flag_error($error)
{
  echo $error;
}
?>
