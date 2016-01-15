@if ($view_type == 'log')
<h3><a href="{{ route('viewExercise', ['exercise_name' => $log_exercise->exercise->exercise_name]) }}">{{ $log_exercise->exercise->exercise_name }}</a></h3>
@elseif ($view_type == 'search')
<p><h3>{{ $log_exercise->log_date->toDateString() }}</h3><a href="{{ route('viewLog', ['date' => $log_exercise->log_date->toDateString()]) }}">View Log</a></p>
@endif
<p class="logrow">
@if ($log_exercise->logex_volume > 0)
    Volume: <span class="heavy">{{ $log_exercise->logex_volume }}</span>{{ $user->user_unit }} - Reps: <span class="heavy">{{ $log_exercise->logex_reps }}</span> - Sets: <span class="heavy">{{ $log_exercise->logex_sets }}</span>
    @if (Auth::user()->user_showintensity != 'h')
         - Avg. Intensity: <span class="heavy">{{ $log_exercise->average_intensity }}</span>
    @endif
@endif
</p>
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
            {!! ($log_item->logitem_reps == 0) ? '<del>' : '' !!}
            <span class="heavy">{{ $log_item->display_value }}</span>{{ ($log_item->show_unit) ? $user->user_unit : ''}}
            @if (($log_item->is_time && $log_item->logitem_reps > 1) || !$log_item->is_time)
                x <span class="heavy">{{ $log_item->logitem_reps }}</span>
            @endif
            @if (($log_item->is_time && $log_item->logitem_sets > 1) || !$log_item->is_time)
                x <span class="heavy">{{ $log_item->logitem_sets }}</span>
            @endif
            @if ($log_item->logitem_reps && !$log_item->is_time)
                <small class="leftspace"><i>&#8776; {{ $log_item->logitem_1rm }} {{ ($log_item->show_unit) ? $user->user_unit : ''}}</i></small>
            @endif
            @if ($log_item->logitem_pre != NULL)
                <span class="leftspace">@ {{ $log_item->logitem_pre }}</span>
            @endif
            {!! ($log_item->logitem_reps == 0) ? '</del>' : '' !!}
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
        <td colspan="3">{!! Format::replace_video_urls(nl2br(e($log_exercise->logex_comment))) !!}</td>
    </tr>
</tbody>
</table>
