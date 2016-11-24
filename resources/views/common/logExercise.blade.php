@if ($view_type == 'log')
    @if (Auth::check() && $user->user_id == Auth::user()->user_id)
        <h3><a href="{{ route('viewExercise', ['exercise_name' => $log_exercise->exercise->exercise_name]) }}">{{ $log_exercise->exercise->exercise_name }}</a></h3>
    @else
        <h3 class="exercise">{{ $log_exercise->exercise->exercise_name }}</h3>
    @endif
@elseif ($view_type == 'search')
<p><h3>{{ $log_exercise->log_date->toDateString() }}</h3><a href="{{ route('viewLog', ['date' => $log_exercise->log_date->toDateString()]) }}">View Log</a></p>
@endif
<p class="logrow">
@if ($log_exercise->logex_volume + ($log_exercise->logex_failed_volume * $user->user_volumeincfails) - ($log_exercise->logex_warmup_volume * $user->user_volumewarmup) > 0)
    Volume: <span class="heavy">{{ Format::correct_weight($log_exercise->logex_volume + ($log_exercise->logex_failed_volume * $user->user_volumeincfails) - ($log_exercise->logex_warmup_volume * $user->user_volumewarmup)) }}</span>{{ (Auth::check()) ? Auth::user()->user_unit : 'kg' }} - Reps: <span class="heavy">{{ $log_exercise->logex_reps }}</span> - Sets: <span class="heavy">{{ $log_exercise->logex_sets }}</span>
    @if (Auth::check() && Auth::user()->user_showintensity != 'h')
         - Avg. Intensity: <span class="heavy">{{ $log_exercise->average_intensity }}</span>
    @endif
    @if (Auth::check() && Auth::user()->user_showinol)
         - INoL: <span class="heavy">{{ round((Auth::user()->user_inolincwarmup) ? $log_exercise->logex_inol : ($log_exercise->logex_inol - $log_exercise->logex_inol_warmup), 1) }}</span>
    @endif
@endif
@if ($log_exercise->logex_time > 0)
    <p class="logrow">
        Time: {!! Format::format_time($log_exercise->logex_time, true) !!}</span>
    </p>
@endif
@if($log_exercise->logex_distance > 0)
    <p class="logrow">
        Distance: {!! Format::format_distance($log_exercise->logex_distance, true) !!}</span>
    </p>
@endif
</p>
@if ($log_exercise->logex_comment != '')
<blockquote class="small">
    {!! Format::replace_video_urls(nl2br(e($log_exercise->logex_comment))) !!}
</blockquote>
@endif
<table class="table">
<tbody>
@forelse ($log_exercise->log_items as $log_item)
    <tr class="{{ $log_item->is_pr ? 'alert alert-success' : ''}}{{ ($log_item->logitem_reps == 0) ? 'alert alert-danger' : ''}} {{ (($log_item->is_warmup) ? 'warmup' : '') }}">
        <td class="tdpr">
            @if($log_item->is_pr)
                <span class="glyphicon glyphicon-star" aria-hidden="true"></span>
            @else
                &nbsp;
            @endif
        </td>
        <td class="logrow">
            {!! ($log_item->logitem_reps == 0) ? '<del>' : '' !!}
            <span class="heavy">{{ $log_item->display_value }}</span>{{ ($log_item->show_unit) ? (Auth::check() ? Auth::user()->user_unit : 'kg') : '' }}
            @if ((($log_item->is_time || $log_item->is_distance) && $log_item->logitem_reps > 1) || !($log_item->is_time || $log_item->is_distance))
                x <span class="heavy">{{ $log_item->logitem_reps }}</span>
            @endif
            @if ((($log_item->is_time || $log_item->is_distance) && $log_item->logitem_sets > 1) || !($log_item->is_time || $log_item->is_distance))
                x <span class="heavy">{{ $log_item->logitem_sets }}</span>
            @endif
            @if ($log_item->logitem_reps && !$log_item->is_time && !$log_item->is_distance)
                <small class="leftspace"><i>&#8776; {{ Format::correct_weight($log_item->logitem_1rm) }} {{ (Auth::check()) ? Auth::user()->user_unit : 'kg' }}</i></small>
            @endif
            @if ($log_item->logitem_pre != NULL)
                <span class="leftspace">@ {{ $log_item->logitem_pre }}</span>
            @endif
            {!! ($log_item->logitem_reps == 0) ? '</del>' : '' !!}
            @if ($log_item->logitem_comment != '')
                <blockquote class="small">{{ $log_item->logitem_comment }}</blockquote>
            @endif
        </td>
        <td class="tdpr2">
            @if($log_item->is_pr)
                <span class="heavy">{{ $log_item->logitem_reps }} RM</span>
            @elseif ($log_item->is_warmup)
            	<small><em>warmup</em></small>
            @else
                &nbsp;
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="3">Nothing seems to be here</td>
    </tr>
@endforelse
</tbody>
</table>
