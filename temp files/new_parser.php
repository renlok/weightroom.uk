<?php
$data = array();
$format = '0:0:0';
$accepted_chars = array('0', ':');
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
  }
  $chunk_dump .= $chr;
}
print_r(array($number_dump,
$format_dump,
$chunk_dump));
print_r($data);
?>
