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
			$type_sql_select = ', e.exercise_name, le.logitem_abs_weight';
			$type_sql_join = 'INNER JOIN log_items le ON (le.log_id = l.log_id)
				INNER JOIN exercises e ON (e.exercise_id = le.exercise_id)';
			$type_sql = 'AND (le.exercise_id = :user_squatid OR le.exercise_id = :user_deadliftid OR le.exercise_id = :user_benchid) AND is_pr = 1';
			$params[] = array(':user_squatid', $user->user_data['user_squatid'], 'int');
			$params[] = array(':user_deadliftid', $user->user_data['user_deadliftid'], 'int');
			$params[] = array(':user_benchid', $user->user_data['user_benchid'], 'int');
		}
		elseif ($type == 'sinclair')
		{
			$type_sql_select = ', e.exercise_name, le.logitem_abs_weight';
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
		$graph_array = array(
			'bodyweight' => array(),
		);
		if ($type != '')
		{
			$date_array = array();
		}
		$last_weight = 0; // so we can see when it changes
		$last_exercise = array();
		while ($row = $db->fetch())
		{
			// is it a new weight
			if ($last_weight != $row['log_weight'])
			{
				$graph_array['bodyweight'][$row['log_date']] = $row['log_weight'];
				if ($type != '')
				{
					$date_array[$row['log_date']]['bodyweight'] = $row['log_weight'];
				}
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
				if (floatval($last_exercise[$row['exercise_name']]) < floatval($row['logitem_abs_weight']))
				{
					$graph_array[$row['exercise_name']][$row['log_date']] = $row['logitem_abs_weight'];
					if ($type != '')
					{
						$date_array[$row['log_date']][$row['exercise_name']] = $row['logitem_abs_weight'];
					}
					// set new weight
					$last_exercise[$row['exercise_name']] = $row['logitem_abs_weight'];
				}
			}
		}

		if ($type != '')
		{
			return array($graph_array, $date_array);
		}
		else
		{
			return $graph_array;
		}
	}

  public function build_coefficient_graph_data($graph_data, $coefficient)
  {
    // build an empty array with each of the lifts
		$lift_names = array_keys($graph_data[0]);
		$lift_data = array();
		foreach ($lift_names as $lift)
		{
			$lift_data[$lift] = 0;
		}
		$output_data = array();
		foreach ($graph_data[1] as $date => $exercises)
		{
			// overwrite old values
			$lift_data = array_merge($lift_data, $exercises);
			// each lift must have a value before we can calculate
			if (array_product($lift_data) > 0)
			{
				if ($coefficient == 'wilks')
				{
					$output_data[$date] = $this->calculate_wilks (array_sum($lift_data), $lift_data['bodyweight']);
				}
				else if ($coefficient == 'sinclair')
				{
					$output_data[$date] = $this->calculate_sinclair (array_sum($lift_data), $lift_data['bodyweight']);
				}
			}
		}

    return $output_data;
  }

	public function calculate_wilks ($total, $bw)
	{
		global $user;
		if ($user->user_data['user_gender'] == 1)
		{
			// male Coefficients
			$a = -216.0475144;
			$b = 16.2606339;
			$c = -0.002388645;
			$d = -0.00113732;
			$e = 7.01863E-06;
			$f = -1.291E-08;
		}
		else
		{
			// female Coefficients
			$a = 594.31747775582;
			$b = -27.23842536447;
			$c = 0.82112226871;
			$d = -0.00930733913;
			$e = 0.00004731582;
			$f = -0.00000009054;
		}
		$coeff = 500/($a + $b * $bw + pow($bw, 2) * $c + pow($bw, 3) * $d + pow($bw, 4) * $e + pow($bw, 5) * $f);
		return $coeff * $total;
	}

	public function calculate_sinclair ($total, $bw)
	{
		global $user;
		// valid until RIO 2016
		if ($user->user_data['user_gender'] == 1)
		{
			// male Coefficients
			$a = 0.794358141;
			$b = 174.393;
		}
		else
		{
			// female Coefficients
			$a = 0.897260740;
			$b = 148.026;
		}
		$coeff = pow(10, ($a * pow(log10 ($bw / $b), 2)));
		return $coeff * $total;
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
