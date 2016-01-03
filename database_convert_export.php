<?php
// insert data
$db = new mysqli('localhost', 'user', 'pass', 'demo');
$tables = [
    'exercise_records' => 'exercise_records',
    'exercises' => 'exercises',
    'invite_codes' => 'invite_codes',
    'log_comments' => 'comments',
    'log_exercises' => 'log_exercises',
    'log_items' => 'log_items',
    'logs' => 'logs',
    'notifications' => 'notifications',
    'user_follows' => 'user_follows',
    'users' => 'users'
];
$renamed = [
    'exercise_records' => ['pr_weight' => 'pr_value'],
    'exercises' => [],
    'invite_codes' => ['code_expire' => 'code_expires'],
    'log_comments' => [
        'log_id' => 'commentable_id',
        'log_date' => '',
        'sender_user_id' => 'user_id',
        'receiver_user_id' => ''
    ],
    'log_exercises' => [],
    'log_items' => ['logitem_rpes' => 'logitem_pre'],
    'logs' => [],
    'notifications' => [],
    'user_follows' => ['follow_date' => ''],
    'users' => [
        'user_unit' => '',
        'user_gender' => '',
        'user_showreps' => '',
        'user_showintensity' => ''
    ]
];

foreach ($tables as $old_name => $table)
{
    $query = "SELECT * FROM $old_name;";
    $result = $db->query($query);
    while ($row = $result->fetch_assoc())
    {
        $keys = array_keys($row);
        $values = $colomns = '';
        foreach ($keys as $key)
        {
            if ($values != '')
            {
                $values .= ',';
                $colomns .= ',';
            }
            if (isset($renamed[$old_name][$key]) && $renamed[$old_name][$key] != '')
            {
                $values .= '"' . str_replace('"', '\"', $row[$key]) . '"';
                $colomns .= "`" . $renamed[$old_name][$key] . "`";
            }
            elseif (!isset($renamed[$old_name][$key]))
            {
                $values .= '"' . str_replace('"', '\"', $row[$key]) . '"';
                $colomns .= "`" . $key . "`";
            }
        }
        // print
        echo "INSERT INTO $table ($colomns) VALUES ($values);\n";
    }
}

// calculate is_est1rm
$query = "SELECT * FROM exercise_records ORDER BY user_id, exercise_id, pr_reps, pr_date ASC";
$user_id = $exercise_id = $pr_reps = $max = 0;
$result = $db->query($query);
while ($row = $result->fetch_assoc())
{
    // new set
    if ($user_id != $row['user_id'] || $exercise_id != $row['exercise_id'] || $pr_reps != $row['pr_reps'])
    {
        $user_id = $row['user_id'];
        $exercise_id = $row['exercise_id'];
        $pr_reps = $row['pr_reps'];
        $max = 0;
        echo "UPDATE exercise_records SET is_est1rm = 1 WHERE pr_id = {$row['pr_id']};\n";
    }
    elseif ($row['pr_1rm'] > $max)
    {
        $max = $row['pr_1rm'];
        echo "UPDATE exercise_records SET is_est1rm = 1 WHERE pr_id = {$row['pr_id']};\n";
    }
}

// count log totals
$query = "SELECT log_id, SUM(logex_volume) as logex_volume, SUM(logex_reps) as logex_reps, SUM(logex_sets) as logex_sets FROM  `log_exercises` GROUP BY log_id";
$result = $db->query($query);
while ($row = $result->fetch_assoc())
{
    echo "UPDATE logs SET log_total_volume = {$row['logex_volume']}, log_total_reps = {$row['logex_reps']}, log_total_sets = {$row['logex_sets']} WHERE log_id = {$row['log_id']};\n";
}

// get failed volume
$query = "SELECT SUM(logitem_weight*logitem_sets) as failedvolume, log_id FROM `log_items` WHERE logitem_reps = 0 GROUP BY log_id";
$result = $db->query($query);
while ($row = $result->fetch_assoc())
{
    echo "UPDATE logs SET log_failed_volume = {$row['failedvolume']} WHERE log_id = {$row['log_id']};\n";
}

// get logex_id
$query = "SELECT i.exercise_id, i.log_id, e.logex_id FROM `log_items` i
        JOIN log_exercises e ON (i.user_id = e.user_id AND i.log_id = e.log_id AND i.exercise_id = e.exercise_id)
        GROUP BY i.exercise_id, i.log_id";
$result = $db->query($query);
while ($row = $result->fetch_assoc())
{
    echo "UPDATE log_items SET logex_id = {$row['logex_id']} WHERE exercise_id = {$row['exercise_id']} AND log_id = {$row['log_id']};\n";
}
$db->close();
