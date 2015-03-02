<script src="https://code.jquery.com/jquery-2.1.3.min.js" charset="utf-8"></script>
<link href="http://nazar-pc.github.io/PickMeUp/css/pickmeup.css" rel="stylesheet">
<script src="http://nazar-pc.github.io/PickMeUp/js/jquery.pickmeup.js"></script>

<style>
.cal_log_date{
	background-color:#F90;
}
</style>

<script>
$(function () {
	var arDates = [{LOG_DATES}];
	$('.date').pickmeup({
		date		: new Date({JSDATE}),
		flat		: true,
		format  	: 'Y-m-d',
		change		: function(e){ window.location.href = '?do=view&page=log&date='+e;},
		calendars	: 3,
		render: function(date) {
			if (arDates.indexOf(date.valueOf()) != -1)
			{
				return {
					class_name: 'cal_log_date'                         
				}
			}
		}
	});
	$('.pmu-prev').click();
	$('.pmu-prev').click();
});
</script>

<div class="date"></div>

<p><- <a href="?do=view&page=log&date={YESTERDAY}">{YESTERDAY}</a> | <strong>{DATE}</strong> | <a href="?do=view&page=log&date={TOMORROW}">{TOMORROW}</a> -></p>
<!-- IF B_LOG -->
<p><a href="?do=edit&page=log&date={DATE}">Edit Log</a></p>
<!-- ELSE -->
<p><a href="?do=edit&page=log&date={DATE}">Add Log</a></p>
<!-- ENDIF -->
<!-- BEGIN items -->
	<h1><a href="?page=exercise&ex={items.EXERCISE}">{items.EXERCISE}</a></h1><p>Volume: {items.VOLUME} - Reps: {items.REPS} - Sets: {items.SETS}</p>
	<!-- BEGIN sets -->
		<p>{items.sets.WEIGHT} x {items.sets.REPS} x {items.sets.SETS} - {items.sets.COMMENT}</p>
	<!-- END sets -->
	<p>{items.COMMENT}</p>
<!-- END items -->