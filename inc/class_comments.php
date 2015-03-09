<?php
class comments
{
    
    public $parents  = array();
    public $children = array();
    public $comments = '';

    /**
     * @param array $comments 
     */
    private function construct_comments($comments)
    {
        foreach ($comments as $comment)
        {
            if ($comment['parent_id'] === NULL)
            {
                $this->parents[$comment['comment_id']][] = $comment;
            }
            else
            {
                $this->children[$comment['parent_id']][] = $comment;
            }
        }        
    }

	private function get_tabs($depth)
	{
		$tabs = '';
		for ($depth; $depth > 0; $depth--)
        {
			$tabs .= "\t";
        }
		return $tabs;
	}
   
    /**
     * @param array $comment
     * @param int $depth 
     */
    private function format_comment($comment, $depth)
    {
		$this->comments .= $this->get_tabs($depth);
		$datearr = explode(' ', $comment['comment_date']);
		$today = date('Y-m-d');
		// posted same day
		if ($today == $datearr[0])
		{
			$posted_on = 'at' . $datearr[1];
		}
		else
		{
			$date1 = new DateTime($today);
			$date2 = new DateTime($datearr[0]);
			$interval = $date1->diff($date2);
			if ($interval->y) { $posted_on = $interval->format("%y years ago"); }
			elseif ($interval->m) { $posted_on = $interval->format("%m months ago"); }
			elseif ($interval->d) { $posted_on = $interval->format("%d days ago"); }
		}
		$this->comments .= "<li><div class=\"comment\"><h6>{$comment['user_name']} <small>{$posted_on}</small></h6>{$comment['comment']}</div></li>\n";
    }
    
    /**
     * @param array $comment
     * @param int $depth 
     */ 
    private function print_parent($comment, $depth = 0)
    {
		$tabs = $this->get_tabs($depth);
		if ($depth == 0)
			$this->comments .= $tabs . "<ul id=\"log_comments\">\n";
		else
			$this->comments .= $tabs . "<ul class=\"comment_child\">\n";
        foreach ($comment as $c)
        {
            $this->format_comment($c, $depth);

            if (isset($this->children[$c['comment_id']]))
            {
                $this->print_parent($this->children[$c['comment_id']], $depth + 1);
            }
        }
		$this->comments .= $tabs . "</ul>\n";
    }

    public function print_comments()
    {
        foreach ($this->parents as $c)
        {
            $this->print_parent($c);
        }
    }

	public function load_log_comments($log_id)
	{
		global $db;
		$query = "SELECT c.*, u.user_name FROM log_comments c
				LEFT JOIN users u ON (c.user_id = u.user_id)
				WHERE c.log_id = :log_id ORDER BY c.comment_date DESC";
		$params = array(
			array(':log_id', $log_id, 'int')
		);
		$db->query($query, $params);
		//$db->fetchall();
		$data = $db->fetchall();
		//print_r($db->fetchall());
		//print_r($data);
		$this->construct_comments($data);
	}
}
?>