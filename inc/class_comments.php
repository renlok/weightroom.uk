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
		global $_SESSION;
		$this->comments .= $this->get_tabs($depth);
		$datearr = explode(' ', $comment['comment_date']);
		$today = date('Y-m-d');
		// posted same day
		if ($today == $datearr[0])
		{
			$posted_on = 'at ' . $datearr[1];
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
		$message_box = "<div class=\"comment-reply-box\" style=\"display:none;\"><form action=\"?do=view&page=log&date={$comment['log_date']}&user_id={$comment['receiver_user_id']}#comments\" method=\"post\"><input type=\"hidden\" name=\"log_id\" value=\"{$comment['log_id']}\"><input type=\"hidden\" name=\"parent_id\" value=\"{$comment['comment_id']}\"><input type=\"hidden\" name=\"csrftoken\" value=\"{$_SESSION['csrftoken']}\"><div class=\"form-group\"><textarea class=\"form-control\" rows=\"3\" placeholder=\"Comment\" name=\"comment\" maxlength=\"500\"></textarea><p><small>Max. 500 characters</small></p></div><div class=\"form-group\"><button type=\"submit\" class=\"btn btn-default\">Post</button></div></form></div>";
		$this->comments .= "<li><div class=\"comment\"><h6><a href=\"http://weightroom.uk/?page=log&user_id={$comment['sender_user_id']}\">{$comment['user_name']}</a> <small>{$posted_on}</small></h6>{$comment['comment']}<p class=\"small\"><a href=\"#\" class=\"reply\">reply</a></p>$message_box</div></li>\n";
    }
    
    /**
     * @param array $comment
     * @param int $depth 
     */ 
    private function print_parent($comment, $depth = 0)
    {
		$tabs = $this->get_tabs($depth);
		if ($depth == 0)
			$this->comments .= $tabs . "<ul class=\"log_comments\">\n";
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
				LEFT JOIN users u ON (c.sender_user_id = u.user_id)
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

	public function make_comment($parent_id, $comment, $log_id, $log_date, $user_id)
	{
		global $db, $user;
		$query = "INSERT INTO log_comments (parent_id, comment, log_id, log_date, sender_user_id, receiver_user_id) VALUES (:parent_id, :comment, :log_id, :log_date, :sender_user_id, :receiver_user_id)";
		$params = array(
			array(':parent_id', $parent_id, 'int'),
			array(':comment', $comment, 'str'),
			array(':log_date', $log_date, 'str'),
			array(':log_id', $log_id, 'int'),
			array(':sender_user_id', $user->user_id, 'int'),
			array(':receiver_user_id', $user_id, 'int'),
		);
		$db->query($query, $params);
	}
}
?>