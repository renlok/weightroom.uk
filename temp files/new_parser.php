<?php
$data = array();
$format = array('0:0:0', '0.0');
$format_type = array(
  'T' => array('0:0:0'),
  'W' => array('0.0'),
);
$accepted_chars = array('0', ':', '.');
$accepted_char['T'] = array('0', ':');
$accepted_char['W'] = array('0', '.');
$line = '13:23:56 cock and balls';
$number_dump = '';
$format_dump = '';
$chunk_dump = '';
$string_array = str_split($line);
foreach($string_array as $chr)
{
  $format_chr = (is_numeric($chr)) ? '0' : $chr;
  // check character is in format and empty accepted_chars counts as allowing anything
  if (count($accepted_chars) == 0 || in_array($format_chr, $accepted_chars))
  {
    // check all formats still valid
    $rebuild_accepted_chars = false;
    foreach ($accepted_char as $key => $val)
    {
      if(!in_array($format_chr, $val))
      {
        unset($accepted_char[$key]);
        $rebuild_accepted_chars = true;
      }
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
    $data[] = $chunk_dump;
    // reset the chunk_dump
    $chunk_dump = '';
    // find the options for the next format
    $accepted_chars = array(); // TEMP
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




?>
