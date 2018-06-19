<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <base href="https://weightroom.uk/">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WeightRoom</title>
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/codemirror/5.20.0/codemirror.min.css">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/codemirror/5.20.0/addon/hint/show-hint.min.css">
    <link type="text/css" rel="stylesheet" href="css/materialize.min.css" media="screen,projection"/>
    <link rel="stylesheet" type="text/css" href="css/pwa.css">

    <!-- Add to home screen for Safari on iOS -->
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="https://weightroom.uk/manifest.json">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="apple-mobile-web-app-title" content="WeightRoom">
    <meta name="application-name" content="WeightRoom">
    <meta name="msapplication-TileColor" content="#00aba9">
    <meta name="theme-color" content="#ffffff">

</head>
<body>

<div class="navbar-fixed" role="navigation">
    <nav>
        <div class="nav-wrapper">
            <a href="/m/#" class="brand-logo hide-on-small-only">WeightRoom</a>
            <ul class="right">
                <li><a id="add-log-button"><i class="material-icons">add</i></a></li>
                <li><a id="refresh-log-button"><i class="material-icons">refresh</i></a></li>
                <li><a id="datepicker"><i class="material-icons">event</i></a></li>
                <li><a data-target="slide-out" class="sidenav-trigger"><i class="material-icons">menu</i></a></li>
            </ul>
        </div>
    </nav>
</div>

<ul id="slide-out" class="sidenav">
    <li><a href="#!">RM Calculator</a></li>
    <li><a href="#!">PRE Calculator</a></li>
    <li><a href="#!">WL Ratios</a></li>
</ul>

<main class="main" hidden>
    <h5 id="date" class="center-align"></h5>
    <div id="add-log-wrapper" hidden>
        <div class="card-panel cardTemplate" id="add-log">
            <h2>Add Workout</h2>
            <form action="" method="post" id="log-form">
            <div id="log-box">
                <textarea rows="20" cols="50" name="log" id="log" class="form-control" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"></textarea>
            </div>
            <label for="log-weight">Bodyweight</label>
            <input placeholder="Bodyweight" aria-describedby="bodyweight-addon" name="weight" id="log-weight" type="text" class="validate">
            {!! csrf_field() !!}
            <button class="btn waves-effect waves-light" type="submit" name="action">Submit
                <i class="material-icons right">send</i>
            </button>
            </form>
        </div>
    </div>
    <div id="view-log-wrapper">
        <div class="card-panel cardTemplate" id="log-view">
            <h2>View Workout</h2>
            <div v-if="is_empty">
                There's no workout here yet
            </div>
            <div v-else>
            <p class="logrow">
              <span v-if="log_data.log_total_volume > 0">
              Volume: <span class="heavy">@{{ app.round(log_data.log_total_volume) }}</span>kg - Reps: <span class="heavy">@{{ log_data.log_total_reps }}</span> - Sets: <span class="heavy">@{{ log_data.log_total_sets }}</span>
              - Avg. Intensity: <span class="heavy"></span>
              </span>
                <p class="logrow" v-if="log_data.log_total_time > 0">
                    Time: @{{ log_data.log_total_time }}</span>
                </p>
                <p class="logrow" v-if="log_data.log_total_distance > 0">
                    Distance: @{{ log_data.log_total_distance }}</span>
                </p>
            </p>
            <p class="logrow marginl"><small>Bodyweight: <span class="heavy">@{{ app.round(log_data.log_weight) }}</span>kg</small></p>
            <blockquote v-if="log_data.log_comment != ''">@{{ log_data.log_comment }}</blockquote>
            <div v-for="log_exercise in log_data.log_exercises">
                <h3 class="exercise">@{{ log_exercise.exercise.exercise_name_clean }}</h3>
                <p class="logrow">
                  <span v-if="log_exercise.logex_volume > 0">
                  Volume: <span class="heavy">@{{ app.round(log_exercise.logex_volume) }}</span>kg - Reps: <span class="heavy">@{{ log_exercise.logex_reps }}</span> - Sets: <span class="heavy">@{{ log_exercise.logex_sets }}</span>
                   - Avg. Intensity: <span class="heavy">@{{ log_exercise.average_intensity }}</span>
                   - INoL: <span class="heavy">@{{ app.round(log_exercise.logex_inol) }}</span>
                  </span>
                <p class="logrow" v-if="log_exercise.logex_time > 0">
                    Time: @{{ log_exercise.logex_time }}</span>
                </p>
                <p class="logrow" v-if="log_exercise.logex_distance > 0">
                    Distance: @{{ log_exercise.logex_distance }}</span>
                </p>
                </p>
                <blockquote class="small" v-if="log_exercise.logex_comment != ''">@{{ log_exercise.logex_comment }}</blockquote>
                <table class="table">
                    <tbody>
                    <template v-for="log_item in log_exercise.log_items">
                        <tr class="@{{ log_item.is_pr ? 'alert alert-success' : '' }}@{{ log_item.logitem_reps == 0 ? 'alert alert-danger' : ''}} @{{ log_item.is_warmup ? 'warmup' : '' }}">
                            <td>
                                <span class="glyphicon glyphicon-star" aria-hidden="true" v-if="log_item.is_pr"></span>
                            </td>
                            <td class="logrow">
                                <span class="heavy">@{{ log_item.display_value }}</span>@{{ log_item.show_unit ? 'kg' : '' }}
                                x <span class="heavy">@{{ log_item.logitem_reps }}</span>
                                <span v-if="log_item.logitem_sets > 0">x <span class="heavy">@{{ log_item.logitem_sets }}</span></span>
                                <small class="leftspace" v-if="log_item.logitem_reps && !log_item.is_time && !log_item.is_distance"><i>&#8776; @{{ app.round(log_item.logitem_1rm) }} kg</i></small>
                                <span class="leftspace" v-if="typeof log_item.logitem_pre == 'number'">@ @{{ log_item.logitem_pre }}</span>
                                <blockquote class="small" v-if="log_item.logitem_comment != ''">@{{ log_item.logitem_comment }}</blockquote>
                            </td>
                            <td>
                                <span class="heavy" v-if="log_item.is_pr">1 RM</span>
                            </td>
                        </tr>
                    </template>
                    </tbody>
                </table>
            </div>
            </div>
        </div>
    </div>
</main>

<div class="loader">
    <div class="preloader-wrapper small active">
        <div class="spinner-layer spinner-green-only">
            <div class="circle-clipper left">
                <div class="circle"></div>
            </div><div class="gap-patch">
                <div class="circle"></div>
            </div><div class="circle-clipper right">
                <div class="circle"></div>
            </div>
        </div>
    </div>
</div>

<script>
    var $ELIST = [];
    var $GLIST = [];
</script>
<script src="{{ mix('js/log.edit.js') }}"></script>
<script type="text/javascript" src="js/materialize.min.js"></script>
<script src="js/vue.min.js"></script>
<script src="js/pwa.js"></script>

</body>
</html>
