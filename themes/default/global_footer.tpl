</div>

<!-- Modal -->
<div class="modal fade" id="searchUsers" tabindex="-1" role="dialog" aria-labelledby="searchUsersLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="searchUsersLabel">Search Users</h4>
      </div>
      <div class="modal-body">
		<form class="form-inline" method="post" action="?page=search">
		  <div class="form-group">
			<label class="sr-only">Username</label>
		  </div>
		  <div class="form-group">
			<label for="Username2" class="sr-only">Username</label>
			<input type="text" class="form-control" id="Username2" placeholder="Username" name="username">
		  </div>
		  <input type="hidden" name="csrftoken" value="{_CSRFTOKEN}">
		  <button type="submit" class="btn btn-default">Search</button>
		</form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<footer class="footer">
  <div class="container">
	<p class="text-muted">2015 &#169; weightroom.uk.<span class="hidden-xs"> Use of this site constitutes acceptance of the site's Privacy Policy and Terms of Use.</span></p>
  </div>
</footer>

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-1798088-8', 'auto');
  ga('send', 'pageview');

</script>

</body>
</html>