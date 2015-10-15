<a href="?page=dash">Main</a> | <a href="?page=dash&amp;do=logs_only">Logs only</a> | <a href="?page=dash&amp;do=all_logs">View all</a>
<table class="table">
<tbody>
<!-- BEGIN logs -->
	<tr>
		<td class="logrow">
	<!-- IF logs.TYPE eq 'log' -->
			<a href="http://weightroom.uk/?page=log&user_id={logs.USER_ID}">{logs.USER_NAME}</a> posted a log {logs.POSTED}. <a href="?page=log&user_id={logs.USER_ID}&date={logs.LOG_DATE}">View log</a>
	<!-- ELSEIF logs.TYPE eq 'comment' -->
			<a href="http://weightroom.uk/?page=log&user_id={logs.USER_ID}">{logs.USER_NAME}</a> posted a comment {logs.POSTED} on your <a href="?page=log&date={logs.LOG_DATE}#comments">log</a>
	<!-- ELSEIF logs.TYPE eq 'reply' -->
			<a href="http://weightroom.uk/?page=log&user_id={logs.USER_ID}">{logs.USER_NAME}</a> replied to a comment {logs.POSTED}. <a href="?page=log&user_id={logs.LOG_USER_ID}&date={logs.LOG_DATE}#comments">View reply</a>
	<!-- ENDIF -->
		</td>
	</tr>
<!-- BEGINELSE -->
	<tr>
		<td class="logrow">
			There has been no logs posted by people you follow
		</td>
	</tr>
<!-- END logs -->
</tbody>
</table>
