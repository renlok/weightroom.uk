<?php
class comments
{
    
    public $parents  = array();
    public $children = array();

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
   
    /**
     * @param array $comment
     * @param int $depth 
     */
    private function format_comment($comment, $depth)
    {   
        for ($depth; $depth > 0; $depth--)
        {
            echo "\t";
        }
        
        print($comment['comment']);
        echo "\n";
    }
    
    /**
     * @param array $comment
     * @param int $depth 
     */ 
    private function print_parent($comment, $depth = 0)
    {   
        foreach ($comment as $c)
        {
            $this->format_comment($c, $depth);

            if (isset($this->children[$c['comment_id']]))
            {
                $this->print_parent($this->children[$c['comment_id']], $depth + 1);
            }
        }
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
		$query = "SELECT * FROM log_comments WHERE log_id = :log_id ORDER BY comment_date DESC";
		$params = array(
			array(':log_id', $log_id, 'int')
		);
		$db->query($query, $params);
		$this->construct_comments($db->fetchall());
	}
}
?>