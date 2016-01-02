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
        'log_date' => NULL,
        'comment_date' => NULL
    ],
    'log_exercises' => [],
    'log_items' => ['logitem_rpes' => 'logitem_pre'],
    'logs' => [],
    'notifications' => [],
    'user_follows' => ['follow_date' => NULL],
    'users' => [
        'user_unit' => NULL,
        'user_gender' => NULL,
        'user_showreps' => NULL,
        'user_showintensity' => NULL
    ]
];

foreach ($tables as $old_name => $table)
{
    $query = "SELECT * FROM $old_name;"
    $result = $db->mysqli_query($query);
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
            if (isset($renamed[$old_name][$key]) && $renamed[$old_name][$key] != NULL)
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
        echo "INSERT INTO $table ($colomns) VALUES ($values);\n"
    }
}

$db->close();
