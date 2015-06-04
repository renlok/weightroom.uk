<h2>{EXERCISE}</h2>
<small><a href="?page=exercise&do=list">&larr; Back to list</a></small> | <small><a href="?page=exercise&ex={EXERCISE}">&larr; Back to exercise</a></small>

<div class="panel-group margintb" id="workouthistory" role="tablist" aria-multiselectable="true">
<!-- BEGIN items -->
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="heading{items.LOG_DATE}">
      <h4 class="panel-title">
        <a class="collapsed" data-toggle="collapse" data-parent="#workouthistory" href="#collapse{items.LOG_DATE}" aria-expanded="false" aria-controls="collapse{items.LOG_DATE}">
          {items.LOG_DATE}
        </a>
      </h4>
    </div>
    <div id="collapse{items.LOG_DATE}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading{items.LOG_DATE}">
      <div class="panel-body">
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
      </div>
    </div>
  </div>
<!-- END items -->
</div>

<script src="https://code.jquery.com/jquery-2.1.3.min.js" charset="utf-8"></script>
<script src="http://getbootstrap.com/dist/js/bootstrap.min.js" charset="utf-8"></script>