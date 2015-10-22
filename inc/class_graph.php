<?php
class graph
{
  private $graph_data_open;
  private $graph_data_entry;
  private $graph_data_close;

  public function __construct()
  {
    $this->graph_data_open = "var dataset = [];\n";
    $this->graph_data_entry = "\tdataset.push({x: new Date(%s), y: %f, shape:'circle'});\n";
    $this->graph_data_close = "prHistoryChartData.push({\n\tvalues: dataset,\n\tkey: '%s'\n});\n";
    $this->graph_data_close_multi = "prHistoryChartData.push({\n\tvalues: dataset,\n\tkey: '%s',\n\ttype: \"line\",\n\tyAxis: %d});\n";
  }

  public function get_graph_data($user_id, $type = '', $range = 0)
	{
		global $db, $user;
		$params = array();
		if ($type == 'wilks')
		{
			$type_sql_select = ', e.exercise_name, le.logitem_weight';
			$type_sql_join = 'INNER JOIN log_items le ON (le.log_id = l.log_id)
				INNER JOIN exercises e ON (e.exercise_id = le.exercise_id)';
			$type_sql = 'AND (le.exercise_id = :user_squatid OR le.exercise_id = :user_deadliftid OR le.exercise_id = :user_benchid) AND is_pr = 1';
			$params[] = array(':user_squatid', $user->user_data['user_squatid'], 'int');
			$params[] = array(':user_deadliftid', $user->user_data['user_deadliftid'], 'int');
			$params[] = array(':user_benchid', $user->user_data['user_benchid'], 'int');
		}
		elseif ($type == 'sinclair')
		{
			$type_sql_select = ', e.exercise_name, le.logitem_weight';
			$type_sql_join = 'INNER JOIN log_items le ON (le.log_id = l.log_id)
				INNER JOIN exercises e ON (e.exercise_id = le.exercise_id)';
			$type_sql = 'AND (le.exercise_id = :user_snatchid OR le.exercise_id = :user_cleanjerkid) AND is_pr = 1';
			$params[] = array(':user_snatchid', $user->user_data['user_snatchid'], 'int');
			$params[] = array(':user_cleanjerkid', $user->user_data['user_cleanjerkid'], 'int');
		}
		else
		{
			$type_sql_select = '';
			$type_sql_join = '';
			$type_sql = ' AND log_weight != 0';
		}
		if ($range > 0)
		{
			$type_sql .= ' AND log_date >= :pr_date';
			$params[] = array(':pr_date', date("Y-m-d", strtotime("-$range months")), 'str');
		}
		// load all bodyweight
		$query = "SELECT log_date, log_weight $type_sql_select FROM logs l
				$type_sql_join
				WHERE l.user_id = :user_id
				$type_sql
				ORDER BY log_date ASC";
		$params[] = array(':user_id', $user_id, 'int');
		$db->query($query, $params);
		$return_array = array(
			'bodyweight' => array(),
		);
		$last_weight = 0; // so we can see when it changes
		$last_exercise = array();
		while ($row = $db->fetch())
		{
			// is it a new weight
			if ($last_weight != $row['log_weight'])
			{
				$return_array['bodyweight'][$row['log_date']] = $row['log_weight'];
				// set new weight
				$last_weight = $row['log_weight'];
			}
			// are we including exercises
			if ($type != '')
			{
        // set up the exercise array
        if (!isset($last_exercise[$row['exercise_name']]))
        {
          $last_exercise[$row['exercise_name']] = array();
        }
				if ($last_exercise[$row['exercise_name']] != $row['logitem_weight'])
				{
					$return_array[$row['exercise_name']][$row['log_date']] = $row['logitem_weight'];
					// set new weight
					$last_exercise[$row['exercise_name']] = $row['log_weight'];
				}
			}
		}

    return $return_array;
	}

  public function build_coefficient_graph_data($graph_data, $coefficient)
  {
    // TODO: build this
    // build an ordered array of changes ordered by dates
    $temporary_data = array();

    return $graph_data;
  }

  public function build_graph_data($data, $multichart = false)
	{
		global $user;
		$js_graph_data = '';
    foreach ($data as $type => $type_data)
    {
  		$js_graph_data .= $this->graph_data_open;
  		foreach ($type_data as $date => $weight)
  		{
  			$date = strtotime($date . ' 00:00:00') * 1000;
  			$weight = correct_weight($weight, 'kg', $user->user_data['user_unit']);
        $js_graph_data .= sprintf($this->graph_data_entry, $date, $weight);
  		}
      if ($multichart)
      {
        $js_graph_data .= sprintf($this->graph_data_close_multi, ucwords($type), 1);
      }
      else
      {
        $js_graph_data .= sprintf($this->graph_data_close, ucwords($type));
      }
    }
		return $js_graph_data;
	}
}
?>
