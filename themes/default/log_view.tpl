<p><- <a href="?do=edit&page=log&date={YESTERDAY}">{YESTERDAY}</a> | <strong>{DATE}</strong> | <a href="?do=view&page=log&date={TOMORROW}">{TOMORROW}</a> -></p>
<p><a href="?do=edit&page=log&date={DATE}">Edit Log</a></p>
<!-- BEGIN items -->
	<h1><a href="?page=exercise&ex={items.EXERCISE}">{items.EXERCISE}</a></h1><p>Volume: {items.VOLUME} - Reps: {items.REPS} - Sets: {items.SETS}</p>
	<!-- BEGIN sets -->
		<p>{items.sets.WEIGHT} x {items.sets.REPS} x {items.sets.SETS} - {items.sets.COMMENT}</p>
	<!-- END sets -->
	<p>{items.COMMENT}</p>
<!-- END items -->