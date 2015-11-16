<style>
.pmu-not-in-month.cal_log_date{
	background-color:#7F4C00;
}
.cal_log_date{
	background-color:#F90;
}
.log_comments, .comment_child {
	list-style: none;
}
.comment_child {
	padding-left:10px;
}
.comment_child li div {
	border-left: solid 1px #ddd;
	padding-left: 10px;
}
.comment h6 {
	margin-bottom: 0px;
}
.jcollapsible:hover, .jcollapsible:visited, .jcollapsible {
	text-decoration: none;
}
.calender-cont {
	text-align: center;
}
.user-info {
	width: 150px;
}
.form-group, .comment-reply-box {
	border-left: none !important;
}
.leftspace {
	margin-left: 10px;
}
</style>

<div class="container-fluid">
	<div class="row">
		<div class="col-md-3 col-md-push-9">
		<div class="user-info">
			<h4>{USERNAME} {BADGES}</h4>
			<p><small>Member since: {JOINED}</small></p>
<!-- IF B_NOSELF -->
	<!-- IF B_FOLLOWING -->
			<p class="btn btn-default"><a href="?do=view&page=log&date={DATE}&user_id={USER_ID}&follow=false">Unfollow <img src="img/user_delete.png"></a></p>
	<!-- ELSE -->
			<p class="btn btn-default"><a href="?do=view&page=log&date={DATE}&user_id={USER_ID}&follow=true">Follow <img src="img/user_add.png"></a></p>
	<!-- ENDIF -->
<!-- ENDIF -->
		</div>
		</div>
		<div class="col-md-9 col-md-pull-3">
		<div class="calender-cont" style="max-width: 640px;">
			<p class="hidden-xs"><- <a href="?do=view&page=log&date={YESTERDAY}<!-- IF B_NOSELF -->&user_id={USER_ID}<!-- ENDIF -->">{YESTERDAY}</a> | <strong>{DATE}</strong> | <a href="?do=view&page=log&date={TOMORROW}<!-- IF B_NOSELF -->&user_id={USER_ID}<!-- ENDIF -->">{TOMORROW}</a> -></p>
			<div class="date"></div>
		</div>
		</div>
	</div>
</div>

<!-- IF ! B_NOSELF -->
	<!-- IF B_LOG -->
<p class="margintb"><a href="?do=edit&page=log&date={DATE}" class="btn btn-default">Edit Log</a></p>
	<!-- ELSE -->
<p class="margintb"><a href="?do=edit&page=log&date={DATE}" class="btn btn-default">Add Log</a></p>
	<!-- ENDIF -->
<!-- ENDIF -->
<!-- IF COMMENT ne '' -->
<div class="panel panel-default">
	<div class="panel-body">
		{COMMENT}
	</div>
</div>
<!-- ENDIF -->
<!-- IF B_LOG -->
<h3>Workout summary</h3>
<p class="logrow">Volume: <span class="heavy">{TOTAL_VOLUME}</span>{WEIGHT_UNIT} - Reps: <span class="heavy">{TOTAL_REPS}</span> - Sets: <span class="heavy">{TOTAL_SETS}</span> - Avg. Intensity: <span class="heavy">{TOTAL_INT} <!-- IF AVG_INTENSITY_TYPE eq 0 -->%<!-- ELSEIF AVG_INTENSITY_TYPE eq 1 -->{WEIGHT_UNIT}<!-- ENDIF --></span></p>
<p class="logrow marginl"><small>Bodyweight: <span class="heavy">{USER_BW}</span>{WEIGHT_UNIT}</small></p>
<!-- ENDIF -->
<!-- BEGIN items -->
	<h3><a href="?page=exercise&ex={items.EXERCISE}">{items.EXERCISE}</a></h3>
	<p class="logrow">Volume: <span class="heavy">{items.VOLUME}</span>{WEIGHT_UNIT} - Reps: <span class="heavy">{items.REPS}</span> - Sets: <span class="heavy">{items.SETS}</span> - Avg. Intensity: <span class="heavy">{items.AVG_INT} <!-- IF AVG_INTENSITY_TYPE eq 0 -->%<!-- ELSE -->{WEIGHT_UNIT}<!-- ENDIF --></span></p>
	<table class="table">
	<tbody>
	<!-- BEGIN sets -->
		<tr<!-- IF items.sets.IS_PR --> class="alert alert-success"<!-- ENDIF --><!-- IF items.sets.REPS eq 0 --> class="alert alert-danger"<!-- ENDIF -->>
			<td class="tdpr">
				<!-- IF items.sets.IS_PR --><span class="glyphicon glyphicon-star" aria-hidden="true"></span><!-- ELSE -->&nbsp;<!-- ENDIF -->
			</td>
			<td class="logrow">
				<!-- IF items.sets.REPS eq 0 --><del><!-- ENDIF --><span class="heavy">{items.sets.WEIGHT}</span><!-- IF items.sets.SHOW_UNIT -->{WEIGHT_UNIT}<!-- ENDIF --><!-- IF items.sets.IS_TIME eq 1 and items.sets.REPS gt 1 --> x <span class="heavy">{items.sets.REPS}</span><!-- ENDIF --><!-- IF items.sets.IS_TIME eq 1 and items.sets.SETS gt 1 --> x <span class="heavy">{items.sets.SETS}</span><!-- ENDIF --><!-- IF items.sets.REPS eq 0 --></del><!-- ELSEIF items.sets.REPS gt 1 && items.sets.SHOW_UNIT --> <small class="leftspace"><i>&#8776; {items.sets.EST1RM} {WEIGHT_UNIT}</i></small><!-- ENDIF --><!-- IF items.sets.RPES ne NULL --> @ {items.sets.RPES}<!-- ENDIF -->
				<!-- IF items.sets.COMMENT ne '' --><div class="well well-sm">{items.sets.COMMENT}</div><!-- ENDIF -->
			</td>
			<td class="tdpr2">
				<!-- IF items.sets.IS_PR --><span class="heavy">{items.sets.REPS} RM</span><!-- ELSE -->&nbsp;<!-- ENDIF -->
			</td>
		</tr>
	<!-- END sets -->
		<tr>
			<td colspan="3">{items.COMMENT}</td>
		</tr>
	</tbody>
	</table>
<!-- END items -->
<!-- IF B_LOG -->
<a name="comments"></a>
{LOG_COMMENTS}
<form action="?do=view&page=log&date={DATE}&user_id={USER_ID}#comments" method="post">
<input type="hidden" name="log_id" value="{LOG_ID}">
<input type="hidden" name="parent_id" value="0">
<input type="hidden" name="csrftoken" value="{_CSRFTOKEN}">
<div class="form-group">
	<textarea class="form-control" rows="3" placeholder="Comment" name="comment" maxlength="500"></textarea>
	<p><small>Max. 500 characters</small></p>
</div>
<div class="form-group">
	<button type="submit" class="btn btn-default">Post</button>
</div>
</form>
<!-- ENDIF -->


<link href="http://weightroom.uk/css/pickmeup.css" rel="stylesheet">
<script src="js/jquery.pickmeup.js"></script>
<script src="http://momentjs.com/downloads/moment.js"></script>
<script src="js/jCollapsible.js"></script>

<script>
var calendar_count = 3;
$(document).ready(function(){
	$('.log_comments').collapsible({xoffset:'-30', symbolhide:'[-]', symbolshow:'[+]'<!-- IF COMMENTING -->, defaulthide:false<!-- ENDIF -->});
	$('.reply').click(function() {
		var parent_id = $(this).attr('id');
		var element = $(this).parent().parent().find(".comment-reply-box").first();
		if ( element.is( ":hidden" ) ) {
			element.slideDown("slow");
		} else {
			element.slideUp("slow");
		}
		return false;
	});
	if ($( window ).width() < 500)
	{
		// that window is small
		$(".calender-cont").removeAttr('style');
		calendar_count = 1;
	}
	var arDates = [];
	var calMonths = [];

	$(function () {
		$('.date').pickmeup({
			date		: moment('{DATE}','YYYY-MM-DD').format(),
			flat		: true,
			format  	: 'Y-m-d',
			change		: function(e){ window.location.href = '?do=view&page=log<!-- IF B_NOSELF -->&user_id={USER_ID}<!-- ENDIF -->&date='+e;},
			calendars	: calendar_count,
			first_day	: {WEEK_START},
			render: function(date) {
				var d = moment(date);
				var m = d.format('YYYY-MM');
				if ($.inArray(m, calMonths) == -1)
				{
					calMonths.push(m);
					loadlogdata(m);
				}
				if ($.inArray(d.format('YYYY-MM-DD'), arDates) != -1)
				{
					return {
						class_name: 'cal_log_date'
					}
				}
			}
		});
	});

	function loadlogdata(date)
	{
		$.ajax({
			url: "index.php",
			data: {
				page: 'ajax',
				do: 'cal',
				date: date,
				user_id: {USER_ID}
			},
			type: 'GET',
			dataType: 'json',
			cache: false
		}).done(function(o) {
			$.merge(calMonths, o.cals);
			$.merge(arDates, o.dates);
			$('.date').pickmeup('update');
		}).fail(function() {}).always(function() {});
	}
});
</script>
