<h2>Search logs</h2>
<p>Find logs that meet the following criteria:</p>

<form class="form-horizontal" action="" method="get">
  <div class="form-group">
    <label for="show" class="col-sm-2 control-label">Show</label>
    <div class="col-sm-10">
    <select class="form-control" name="show" id="show">
	  <option value="1"<!-- IF SHOW eq 1 --> selected="selected"<!-- ENDIF -->>the last log</option>
	  <option value="5"<!-- IF SHOW eq 5 --> selected="selected"<!-- ENDIF -->>the last five logs</option>
	  <option value="10"<!-- IF SHOW eq 10 --> selected="selected"<!-- ENDIF -->>the last ten logs</option>
	  <option value="0"<!-- IF SHOW eq 0 --> selected="selected"<!-- ENDIF -->>every log</option>
	</select>
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-10 col-md-offset-2">
      <p class="form-control-static">which meet these criteria</p>
    </div>
  </div>
  <div class="form-group">
    <label for="exercise" class="col-sm-2 control-label">Exercise</label>
    <div class="col-sm-10">
    <select class="form-control" name="exercise" id="exercise">
<!-- BEGIN exercise -->
	<option value="{exercise.EXERCISE}"<!-- IF exercise.SELECTED --> selected="selected"<!-- ENDIF -->>{exercise.EXERCISE}</option>
<!-- END exercise -->
	</select>
    </div>
  </div>
  <div class="form-group">
    <label for="weight" class="col-sm-2 control-label">Weight</label>
    <div class="col-sm-10">
    <div class="input-group">
      <input type="text" class="form-control" name="weight" id="weight" <!-- IF WEIGHT eq 0 -->placeholder="weight"<!-- ELSE -->value="{WEIGHT}"<!-- ENDIF -->>
	  <div class="input-group-addon">{WEIGHT_UNIT}</div>
	</div>
    </div>
  </div>
  <div class="form-group">
    <label for="reps" class="col-sm-2 control-label">Reps</label>
    <div class="col-sm-10">
    <input type="text" class="form-control" name="reps" id="reps" <!-- IF REPS eq '' -->placeholder="any or a number"<!-- ELSE -->value="{REPS}"<!-- ENDIF -->>
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
      <input type="hidden" name="page" value="search_log">
      <button type="submit" class="btn btn-default">Search</button>
    </div>
  </div>
</form>

<!-- BEGIN items -->
	<div class="panel-body">
		<p><h3>{items.LOG_DATE}</h3><a href="?do=view&page=log&date={items.LOG_DATE}">View Log</a></p>
        <p class="logrow">Volume: <span class="heavy">{items.VOLUME}</span>{WEIGHT_UNIT} - Reps: <span class="heavy">{items.REPS}</span> - Sets: <span class="heavy">{items.SETS}</span> - Avg. Intensity: <span class="heavy">{items.AVG_INT} <!-- IF AVG_INTENSITY_TYPE eq 0 -->%<!-- ELSE -->{WEIGHT_UNIT}<!-- ENDIF --></span></p>
		<table class="table">
		<tbody>
		<!-- BEGIN sets -->
			<tr<!-- IF items.sets.IS_PR --> class="alert alert-success"<!-- ENDIF --><!-- IF items.sets.REPS eq 0 --> class="alert alert-danger"<!-- ENDIF -->>
				<td class="tdpr">
					<!-- IF items.sets.IS_PR --><span class="glyphicon glyphicon-star" aria-hidden="true"></span><!-- ELSE -->&nbsp;<!-- ENDIF -->
				</td>
				<td class="logrow">
					<!-- IF items.sets.REPS eq 0 --><del><!-- ENDIF --><span class="heavy">{items.sets.WEIGHT}</span><!-- IF items.sets.SHOW_UNIT -->{WEIGHT_UNIT}<!-- ENDIF --> x <span class="heavy">{items.sets.REPS}</span> x <span class="heavy">{items.sets.SETS}</span><!-- IF items.sets.REPS eq 0 --></del><!-- ELSEIF items.sets.REPS gt 1 && items.sets.SHOW_UNIT --> <small class="leftspace"><i>&#8776; {items.sets.EST1RM} {WEIGHT_UNIT}</i></small><!-- ENDIF --><!-- IF items.sets.RPES ne NULL --> @ {items.sets.RPES}<!-- ENDIF -->
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
      </div>
<!-- END items -->