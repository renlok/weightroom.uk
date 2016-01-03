<h3><a href="{{ route('viewExercise', ['exercise_name' => $log_exercise->exercise->exercise_name]) }}">{{ $log_exercise->exercise->exercise_name }}</a></h3>
<p class="logrow">Volume: <span class="heavy">{{ $log_exercise->logex_volume }}</span>{{ $user->user_unit }} - Reps: <span class="heavy">{{ $log_exercise->logex_reps }}</span> - Sets: <span class="heavy">{{ $log_exercise->logex_sets }}</span> - Avg. Intensity: <span class="heavy">{items.AVG_INT} <!-- IF AVG_INTENSITY_TYPE eq 0 -->%<!-- ELSE -->{WEIGHT_UNIT}<!-- ENDIF --></span></p>
<table class="table">
<tbody>
@foreach ($log_exercise->log_items as $log_item)
    <tr class="{{ $log_item->is_pr ? 'alert alert-success' : ''}}{{ ($log_item->logitem_reps == 0) ? 'alert alert-danger' : ''}}">
        <td class="tdpr">
            @if($log_item->is_pr)
                <span class="glyphicon glyphicon-star" aria-hidden="true"></span>
            @else
                &nbsp;
            @endif
        </td>
        <td class="logrow">
            {{ ($log_item->logitem_reps == 0) ? '<del>' : ''}}
            <span class="heavy">{{ $log_item->display_value }}</span>{{ ($log_item->show_unit) ? $user->user_unit : ''}}
            @if (($log_item->is_time && $log_item->logitem_reps > 1) || !$log_item->is_time)
                x <span class="heavy">{{ $log_item->logitem_reps }}</span>
            @endif
            @if (($log_item->is_time && $log_item->logitem_sets > 1) || !$log_item->is_time)
                x <span class="heavy">{{ $log_item->logitem_sets }}</span>
            @endif
            @if ($log_item->logitem_reps)
                <small class="leftspace"><i>&#8776; {{ $log_item->logitem_1rm }} {{ ($log_item->show_unit) ? $user->user_unit : ''}}</i></small>
            @endif
            @if ($log_item->logitem_pre != NULL)
                <span class="leftspace">@ {{ $log_item->logitem_pre }}</span>
            @endif
            {{ ($log_item->logitem_reps == 0) ? '</del>' : ''}}
            @if ($log_item->logitem_comment != '')
                <div class="well well-sm">{{ $log_item->logitem_comment }}</div>
            @endif
        </td>
        <td class="tdpr2">
            @if($log_item->is_pr)
                <span class="heavy">{{ $log_item->logitem_reps }} RM</span>
            @else
                &nbsp;
            @endif
        </td>
    </tr>
@endforeach
    <tr>
        <td colspan="3">{{ $log_exercise->logex_comment }}</td>
    </tr>
</tbody>
</table>
