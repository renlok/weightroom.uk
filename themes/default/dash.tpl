<table class="table">
<tbody>
<!-- BEGIN logs -->
	<tr>
		<td class="logrow">
			<a href="http://weightroom.uk/?page=log&user_id={logs.USER_ID}">{logs.USER_NAME}</a> posted a log {logs.POSTED}. <a href="?page=log&user_id={logs.USER_ID}&date={logs.LOG_DATE}">View log</a>
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