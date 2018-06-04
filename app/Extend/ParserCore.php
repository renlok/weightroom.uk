<?php

namespace App\Extend;

class ParserCore
{
    // the predefined data variables
    private $units; // array of units for each block type
    private $all_possible_formats; // all possible formats for all possible blocks
    private $next_values_all; // flags that show a certain type of block is coming up
    private $format_follows; // what should come after each block type
    // the working variables
    private $accepted_chars;
    private $possible_formats;
    private $current_blocks; // the blocks that are expected
    private $next_values;
    // data dumps
    private $format_dump;
    private $chunk_dump;
    // final array
    protected $log_data;
    protected $log_text;
    // set some default values
    protected $log_update_text = 0;

    public function __construct($log_text)
    {
        // build the initial startup data
        $this->log_text = $log_text . ' '; //TODO:make a proper fix
        $this->construct_globals ();
    }

    public function parseText ()
    {
        $exercise = '';
        $exercise_number = -1; // a pointer for when exercise was done
        $this->log_data = array('comment' => '', 'exercises' => array()); // the output array
        // convert log_text to array
        $log_lines = explode("\n", $this->log_text);
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
                $exercise_number++;
                preg_match_all('/#([^#])+/', $line, $matches);
                array_walk($matches[0], function(&$item) {
                    $item = substr(trim($item), 1);
                });
                if(!empty($matches[0][0]))
                {
                    $exercise = $matches[0][0]; // set exercise marker
                    unset($matches[0][0]);
                    // add new exercise group to array
                    $this->log_data['exercises'][$exercise_number] = array(
                        'name' => trim($exercise),
                        'comment' => '',
                        'groups' => (is_array($matches[0])) ? array_filter($matches[0]) : [],
                        'data' => array());
                    continue; // end this loop
                }
            }

            // no exercise yet
            if ($exercise == '')
            {
                if (!empty($this->log_data['comment']))
                {
                    $this->log_data['comment'] .= "\n";
                }
                $this->log_data['comment'] .= $line;
                continue; // end this loop
            }
            // line starts with a number or B
            elseif (is_numeric($line[0]) || strtoupper($line[0]) == 'B')
            {
                $this->parseLine ($line, $exercise_number);
            }
            else
            {
                $this->log_data['exercises'][$exercise_number]['comment'] .= $line . "\n";
            }
        }
    }

    /**
     * @param $line
     * @param $position
     */
    private function parseLine ($line, $position)
    {
        // build the initial startup data
        $this->format_dump = '';
        $this->chunk_dump = '';
        $this->current_blocks = array('U', 'W', 'D', 'T', 'C');
        $this->loadPossibleFormatsList ();
        $this->buildAcceptedCharacters ();
        // for when a line contains commas we can add them as multiple sets
        $multiline = 0;
        $multiline_max = 0;
        $output_data = array();
        $line = $this->clean_line ($line);
        $string_array = str_split($line);
        foreach($string_array as $chr)
        {
            // if the last and current characters are spaces just add it to the chunk and continue
            if ($chr == ' ')
            {
                $this->chunk_dump .= ' ';
                continue;
            }
            $format_chr = $this->format_character ($chr);
            // check character is in format and empty accepted_chars counts as allowing anything
            if (count($this->accepted_chars) == 0 || (in_array($format_chr, $this->accepted_chars) && $this->check_keeps_format($format_chr)))
            {
                if ($format_chr == '0')
                {
                    // check the last value of the format_dump if its not 0 already set it to 0
                    if (!isset($this->format_dump[strlen($this->format_dump) - 1]) || $this->format_dump[strlen($this->format_dump) - 1] != '0')
                    {
                        $this->format_dump .= '0';
                    }
                }
                else
                {
                    // the character is not a number so just add it to the format
                    $this->format_dump .= $format_chr;
                }
                $this->chunk_dump .= $chr;
                $this->buildAcceptedCharacters ();
            }
            else
            {
                // check the previous chunk is valid
                if ($this->format_check())
                {
                    // the current chunk has finished do something
                    $output_data[$multiline][$this->current_blocks[0]] = $this->clean_units(trim($this->chunk_dump), $this->current_blocks[0]);
                    // reset the dumps
                    $this->format_dump = '';
                    $this->chunk_dump = '';
                    // we are repeating the chunk
                    if ($this->current_blocks[0] != 'C' && $format_chr == ',')
                    {
                        $multiline++;
                        // new multiline_max?
                        $multiline_max = max($multiline_max, $multiline);
                        // multilines must retain the same format
                        $multiline_block = $this->current_blocks[0];
                        $this->current_blocks = array();
                        $this->current_blocks[0] = $multiline_block;
                        // reset $format_type + next values
                        $this->loadPossibleFormatsList ();
                        // rebuild everything
                        $this->buildAcceptedCharacters ();
                    }
                    else
                    {
                        // find the options for the next format
                        if (in_array($format_chr, $this->next_values))
                        {
                            // find what block comes next
                            $this->current_blocks = array_keys ($this->next_values, $format_chr);
                            // reset $format_type + next values
                            $this->loadPossibleFormatsList ();
                            // rebuild everything
                            $this->buildAcceptedCharacters ();
                        }
                        else
                        {
                            // assume it is a comment
                            $this->assumeComment();
                            $this->chunk_dump .= $chr;
                        }
                        $multiline = 0;
                    }
                }
                else
                {
                    // the chuck is no longer valid assumes its a comment
                    $this->assumeComment();
                    $this->chunk_dump .= $chr;
                }
            }
        }
        // add the last chunk to the data array
        if (!empty($this->chunk_dump))
        {
            if ($this->current_blocks[0] != 'C')
            {
                // TODO: not sure this works
                if ($this->format_check())
                {
                    $this->chunk_dump = $this->clean_units($this->chunk_dump, $this->current_blocks[0]);
                }
                else
                {
                    // final chunk not valid assume its a comment
                    $this->assumeComment();
                }
            }
            if (isset($output_data[$multiline][$this->current_blocks[0]]))
            {
                if (is_array($this->chunk_dump))
                {
                    $output_data[$multiline][$this->current_blocks[0]][0] .= $this->chunk_dump[0];
                    $output_data[$multiline][$this->current_blocks[0]][1] .= $this->chunk_dump[1];
                }
                else
                {
                    $output_data[$multiline][$this->current_blocks[0]] .= $this->chunk_dump;
                }
            }
            else
            {
                $output_data[$multiline][$this->current_blocks[0]] = $this->chunk_dump;
            }
        }

        $this->cleanMultilineData($output_data, $multiline_max);

        // check isn't just a comment
        if (isset($output_data[$multiline_max]['C']) && !empty($output_data[$multiline_max]['C']) && count($output_data[$multiline_max]) == 1)
        {
            $this->log_data['exercises'][$position]['comment'] .= $output_data[$multiline_max]['C'] . "\n";
            unset($output_data[$multiline_max]);
        }

        if (count($output_data) > 0)
        {
            // update log_data variable
            $this->log_data['exercises'][$position]['data'] = array_merge($this->log_data['exercises'][$position]['data'], $output_data);
        }
    }

    /**
     * Holds Unit data for chuck checking
     */
    private function construct_globals ()
    {
        // pre-defined data
        $this->units = array(
            'W' => array(
                'kgs' => 'kg',
                'kg' => 'kg',
                'lbs' => 'lb',
                'lb' => 'lb',
            ),
            'D' => array(
                'kms' => 'km',
                'km' => 'km',
                'ms' => 'm',
                'm' => 'm',
                'miles' => 'mile',
                'mile' => 'mile',
            ),
            'T' => array(
                'seconds' => 's',
                'second' => 's',
                'secs' => 's',
                'sec' => 's',
                'minutes' => 'm',
                'minute' => 'm',
                'mins' => 'm',
                'min' => 'm',
                'm' => 'm',
                'hours' => 'h',
                'hour' => 'h',
                'hrs' => 'h',
                'hr' => 'h',
                'h' => 'h',
                's' => 's', // needs to be at the end or it ruins the party
            )
        );
        $this->all_possible_formats = array(
            'U' => array(('0')),
            'W' => array(
                ('0.0'),
                ('0'),
                ('bw'),
                ('bw+0.0'),
                ('bw+0'),
                ('bw-0.0'),
                ('bw-0'),
            ),
            'D' => array(
                ('0.0'),
                ('0'),
            ),
            'T' => array(
                ('0:0:0'),
                ('0:0'),
                ('0.0'),
                ('0'),
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
        // comment can follow anything
        $this->format_follows = array(
            'U' => array('R', 'P'),
            'W' => array('R', 'P'),
            'D' => array('R', 'P'),
            'T' => array('R', 'P'),
            'R' => array('S', 'P'),
            'S' => array('P'),
            'P' => array(''),
            'C' => array(''),
        );
    }

    private function assumeComment()
    {
        $this->accepted_chars = array();
        $this->current_blocks = array();
        $this->current_blocks[0] = 'C';
    }

    /**
     * Create arrays of all possible formats for currently possible blocks and array for possible block flags
     * Run each time you get a new empty chunk
     */
    private function loadPossibleFormatsList ()
    {
        $this->possible_formats = array();
        $this->next_values = array();
        // loop through current possible formats the current chunk still matches
        foreach ($this->current_blocks as $key)
        {
            // add add possible formats for this block type to format_type
            $this->possible_formats[$key] = $this->all_possible_formats[$key];
            $this->next_values = array_merge($this->next_values, array_intersect_key($this->next_values_all, array_flip($this->format_follows[$key])));
        }
    }

    /**
     * Rebuild accepted_chars array based off the current list of possible formats
     */
    private function buildAcceptedCharacters ()
    {
        // leave if we are looking at a comment
        if ($this->current_blocks[0] == 'C')
            return;
        $this->accepted_chars = array();
        // if current character is number read it to possible characters
        if ($this->format_dump != null && mb_substr($this->format_dump, -1) == 0) {
            $this->accepted_chars[] = 0;
        }
        // get length of format string
        $format_length = mb_strlen($this->format_dump);
        // check all formats still valid
        foreach ($this->possible_formats as $block => $formats)
        {
            foreach ($formats as $i => $format) {
                // check current format dump matches possible format
                if ($format_length > 0 && mb_substr($formats[$i], 0, $format_length) != $this->format_dump) {
                    unset($this->possible_formats[$block][$i]);
                } else {
                    // add next character to accepted characters
                    if ($this->accepted_chars != $formats[$i])
                    {
                        $this->accepted_chars[] = mb_substr($formats[$i], $format_length, 1);
                    }
                }
            }
            // remove newly empty blocks
            if ($block != 'C' && count($this->possible_formats[$block]) == 0)
            {
                unset($this->possible_formats[$block]);
                $pos = array_search($block, $this->current_blocks);
                unset($this->current_blocks[$pos]);
                $this->current_blocks = array_values($this->current_blocks);
            }
        }
        // delete duplicate characters
        $this->accepted_chars = array_unique($this->accepted_chars);
    }

    /**
     * @param $output_data
     * @param $multiline_max
     */
    private function cleanMultilineData(&$output_data, $multiline_max)
    {
        // clean up the multiline data if needed
        if ($multiline_max > 0)
        {
            // temporally remove the comment
            if (isset($output_data[0]['C']))
            {
                $comment = $output_data[0]['C'];
                unset($output_data[0]['C']);
            }
            // the first line should always be complete so find what was given
            $blocks = array_keys($output_data[0]);
            $last_values = array();
            $last_row = 0;
            for ($i = 0; $i <= $multiline_max; $i++)
            {
                // check each block
                foreach ($blocks as $block)
                {
                    if (!isset($output_data[$i][$block]))
                    {
                        $output_data[$i][$block] = $last_values[$block];
                    }
                    if (!is_array($output_data[$i][$block]))
                    {
                        $output_data[$i][$block] = trim($output_data[$i][$block]);
                    }
                    else
                    {
                        $output_data[$i][$block][0] = trim($output_data[$i][$block][0]);
                        $output_data[$i][$block][1] = trim($output_data[$i][$block][1]);
                    }
                }
                // merge identical rows the are next to each other
                if (count(array_diff_assoc_recursive($output_data[$i], $last_values)) == 0)
                {
                    if (!isset($output_data[$last_row]['S']))
                    {
                        $output_data[$last_row]['S'] = 2;
                    }
                    else
                    {
                        $output_data[$last_row]['S'] += 1;
                    }
                    unset($output_data[$i]);
                }
                else
                {
                    $last_values = $output_data[$i];
                    $last_row = $i;
                }
            }
            // re add the comment to the end
            $output_data[$last_row]['C'] = (isset($comment)) ? $comment : '';
        }
    }

    /**
     * @return bool
     * check end chunk is valid
     */
    private function format_check()
    {
        // the block is a comment so skip the check
        if ($this->current_blocks[0] == 'C')
        {
            return true;
        }
        // check if the final format_dump matches a vlid format type
        foreach ($this->possible_formats as $sub_type)
        {
            foreach ($sub_type as $key => $format_string)
            {
                //foreach ($val as $format_string)
                if ($format_string == $this->format_dump)
                {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param $format_chr
     * @return bool
     * check if next character keeps chunk valid
     */
    private function check_keeps_format($format_chr)
    {
        // nothing significant has been added
        if (isset($this->format_dump[strlen($this->format_dump) - 1]) && $this->format_dump[strlen($this->format_dump) - 1] == '0' && $format_chr == '0')
        {
            return true;
        }
        // check if the current format_dump matches a vlid format type
        $format_string = $this->format_dump . $format_chr;
        foreach ($this->possible_formats as $sub_type)
        {
            foreach($sub_type as $format)
            {
                if(stristr($format, $format_string) !== false)
                {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     *
     */
    private function add_units()
    {
        $dump_all_format_types = $this->all_possible_formats;
        foreach ($this->units as $type => $unit_types)
        {
            foreach ($unit_types as $unit => $val)
            {
                foreach ($dump_all_format_types[$type] as $format)
                {
                    $this->all_possible_formats[$type][] = $format . $unit;
                }
            }
        }
    }

    /**
     * @param $block
     * @param $block_type
     * @return array
     */
    private function clean_units($block, $block_type)
    {
        // clean units
        if (isset($this->units[$block_type]))
        {
            $cleaned_block = strtolower(preg_replace('/\s/', '', $block));
            $end = $this->strposa($cleaned_block, array_keys($this->units[$block_type]));
            if ($end !== false)
            {
                // TODO: fix trim(substr($block, $end)), $block set incorrectly
                $block = array(substr($cleaned_block, 0, $end), $this->units[$block_type][substr($cleaned_block, $end)]);
            }
            else
            {
                $block = array($cleaned_block, '');
            }
        }
        return $block;
    }

    /**
     * @param $haystack
     * @param $needle
     * @param int $offset
     * @return bool|int
     */
    private function strposa($haystack, $needle, $offset=0)
    {
        if(!is_array($needle)) $needle = array($needle);
        foreach($needle as $query)
        {
            if(($return = strpos($haystack, $query, $offset)) !== false) return $return; // stop on first true result
        }
        return false;
    }

    /**
     * @param $chr
     * @return string
     */
    private function format_character ($chr)
    {
        $output_chr = $chr;
        // is number
        if (is_numeric($chr))
        {
            $output_chr = '0';
        }
        $output_chr = strtolower($output_chr);
        return $output_chr;
    }

    /**
     * @param $line
     * @return mixed
     */
    private function clean_line ($line)
    {
        // search x
        $line = str_replace (['*', '×'], 'x', $line);
        return $line;
    }

    /**
     * @param $error
     */
    private function flag_error($error)
    {
        echo $error;
    }
}

// useful functions
function array_diff_assoc_recursive($array1, $array2)
{
    $difference = array();
    foreach($array1 as $key => $value)
    {
        if(is_array($value))
        {
            if(!isset($array2[$key]) || !is_array($array2[$key]))
            {
                $difference[$key] = $value;
            }
            else
            {
                $new_diff = array_diff_assoc_recursive($value, $array2[$key]);
                if(!empty($new_diff))
                    $difference[$key] = $new_diff;
            }
        }
        else if(!array_key_exists($key,$array2) || $array2[$key] !== $value)
        {
            $difference[$key] = $value;
        }
    }
    return $difference;
}
