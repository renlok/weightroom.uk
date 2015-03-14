<script src="https://code.jquery.com/jquery-2.1.3.min.js" charset="utf-8"></script>
<link href="http://nazar-pc.github.io/PickMeUp/css/pickmeup.css" rel="stylesheet">
<script src="http://nazar-pc.github.io/PickMeUp/js/jquery.pickmeup.js"></script>
<script src="http://momentjs.com/downloads/moment.js"></script>
<script src="js/jCollapsible.js"></script>

<style>
.cal_log_date{
	background-color:#F90;
}
#log_comments, .comment_child {
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
</style>

<script>
$(document).ready(function(){
	$('#log_comments').collapsible({xoffset:'-30', symbolhide:'[-]', symbolshow:'[+]'});
});

var arDates = [];
var calMonths = [];

$(function () {
	$('.date').pickmeup({
		date		: new Date({JSDATE}),
		flat		: true,
		format  	: 'Y-m-d',
		change		: function(e){ window.location.href = '?do=view&page=log&date='+e;},
		calendars	: 3,
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
</script>

<p><- <a href="?do=view&page=log&date={YESTERDAY}">{YESTERDAY}</a> | <strong>{DATE}</strong> | <a href="?do=view&page=log&date={TOMORROW}">{TOMORROW}</a> -></p>
<div class="date"></div>

<!-- IF B_LOG -->
<p class="margintb"><a href="?do=edit&page=log&date={DATE}" class="btn btn-default">Edit Log</a></p>
<!-- ELSE -->
<p class="margintb"><a href="?do=edit&page=log&date={DATE}" class="btn btn-default">Add Log</a></p>
<!-- ENDIF -->
<!-- IF COMMENT ne '' -->
<div class="panel panel-default">
	<div class="panel-body">
		{COMMENT}
	</div>
</div>
<!-- ENDIF -->
<!-- BEGIN items -->
	<h3><a href="?page=exercise&ex={items.EXERCISE}">{items.EXERCISE}</a></h3>
	<p class="logrow">Volume: <span class="heavy">{items.VOLUME}</span>kg - Reps: <span class="heavy">{items.REPS}</span> - Sets: <span class="heavy">{items.SETS}</span></p>
	<table class="table">
	<tbody>
	<!-- BEGIN sets -->
		<tr<!-- IF items.sets.IS_PR --> class="alert alert-success"<!-- ENDIF --><!-- IF items.sets.REPS eq 0 --> class="alert alert-danger"<!-- ENDIF -->>
			<td class="tdpr">
				<!-- IF items.sets.IS_PR --><span class="glyphicon glyphicon-star" aria-hidden="true"></span><!-- ELSE -->&nbsp;<!-- ENDIF -->
			</td>
			<td class="logrow">
				<!-- IF items.sets.REPS eq 0 --><del><!-- ENDIF --><span class="heavy">{items.sets.WEIGHT}</span>kg x <span class="heavy">{items.sets.REPS}</span> x <span class="heavy">{items.sets.SETS}</span><!-- IF items.sets.REPS eq 0 --></del><!-- ENDIF -->
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
{LOG_COMMENTS}
<form>
<input type="hidden" name="log_id" value="{LOG_ID}">
<input type="hidden" name="csrftoken" value="{_CSRFTOKEN}">
<div class="form-group">
	<textarea class="form-control" rows="3" placeholder="Comment"></textarea>
</div>
<div class="form-group">
	<button type="submit" class="btn btn-default">Post</button>
</div>
</form>
<!-- ENDIF -->
