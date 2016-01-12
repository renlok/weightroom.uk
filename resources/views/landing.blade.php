<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />

    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
    <meta name="viewport" content="width=device-width" />

    <title>Weightroom.uk - Coming soon</title>
	<link rel="icon" type="image/x-icon" href="favicon.ico">
	<meta http-equiv="Content-Language" content="en">

    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.0.2/css/bootstrap.css" rel="stylesheet" />
	<link href="css/coming-sssoon.css" rel="stylesheet" />

    <!--     Fonts     -->
    <link href="//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.css" rel="stylesheet">
    <link href='//fonts.googleapis.com/css?family=Grand+Hotel' rel='stylesheet' type='text/css'>

</head>

<body>
<nav class="navbar navbar-transparent navbar-fixed-top" role="navigation">
  <div class="container">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav navbar-right">
            <li>
                <a href="{{ route('login') }}">
                    Login
                </a>
            </li>
       </ul>

    </div><!-- /.navbar-collapse -->
  </div><!-- /.container -->
</nav>
<div class="main" style="background-image: {{ asset('img/background-1.jpg') }}">

<!--    Change the image source '/images/default.jpg' with your favourite image.     -->

    <div class="cover black" data-color="black"></div>

<!--   You can change the black color for the filter with those colors: blue, green, red, orange       -->

    <div class="container">
        <h1 class="logo cursive">
            Weight Room
						<div class="small">Track dem gains</div>
        </h1>

<!--  H1 can have 2 designs: "logo" and "logo cursive"           -->

        <div class="content">
            <h4 class="motto">
							The greatest weightlifting and powerlifting workout tracker on the web
						</h4>
            <div class="subscribe">
                <h5 class="info-text">
                    Join the waiting list for the beta. We promise to keep you posted.
                </h5>
                <div class="row">
                    <div class="col-md-4 col-md-offset-4 col-sm6-6 col-sm-offset-3 ">
												<form action="//weightroom.us11.list-manage.com/subscribe/post?u=f52d2c2b457fcf8926bf43659&amp;id=65b4a9727f" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="form-inline" role="form" target="_blank" novalidate>
                          <div class="form-group">
                            <label class="sr-only" for="exampleInputEmail2">Email address</label>
                            <input type="email" class="form-control transparent" name="EMAIL" id="mce-EMAIL" placeholder="Your email here...">
                          </div>
                          <button type="submit" class="btn btn-danger btn-fill" name="subscribe">Notify Me</button>
													<div style="position: absolute; left: -5000px;"><input type="text" name="b_f52d2c2b457fcf8926bf43659_65b4a9727f" tabindex="-1" value=""></div>
                        </form>
												<script type='text/javascript' src='//s3.amazonaws.com/downloads.mailchimp.com/js/mc-validate.js'></script><script type='text/javascript'>(function($) {window.fnames = new Array(); window.ftypes = new Array();fnames[0]='EMAIL';ftypes[0]='email';fnames[1]='FNAME';ftypes[1]='text';fnames[2]='LNAME';ftypes[2]='text';}(jQuery));var $mcj = jQuery.noConflict(true);</script>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer">
      <div class="container">
             Theme by <a href="//www.creative-tim.com">Creative Tim</a>.
      </div>
    </div>
 </div>

</body>
	<script src="//code.jquery.com/jquery-2.1.3.min.js" charset="utf-8"></script>
	<script src="//getbootstrap.com/dist/js/bootstrap.min.js" charset="utf-8"></script>
</html>
