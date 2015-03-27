<script src="https://code.jquery.com/jquery-2.1.3.min.js" charset="utf-8"></script>
<script src="http://codemirror.net/lib/codemirror.js"></script>
<link rel="stylesheet" href="http://codemirror.net/lib/codemirror.css">
<link rel="stylesheet" href="http://codemirror.net/addon/hint/show-hint.css">
<script src="http://codemirror.net/addon/mode/overlay.js"></script>
<script src="http://codemirror.net/addon/hint/show-hint.js"></script>
<script>
var $ELIST = [{EXERCISE_LIST}];
function getHints(cm) {
    
    var cur = cm.getCursor(),
        token = cm.getTokenAt(cur),
        str = token.string,
        ustr = $.trim(token.string.substr(1)),
        arr = [],
        list = [];
    if (str.indexOf("#") !== 0) {
        return null;
    }
    for (var i = 0; i < $ELIST.length; i++) {
        if ((ustr == "") || $ELIST[i][0].toLowerCase().indexOf(ustr.toLowerCase()) > -1) {
            arr.push($ELIST[i]);
        }
    }
    arr.sort();
    for (i = 0; i < arr.length; i++) {
        list.push("#" + arr[i][0] + " ");
    }
    var t = "#" + ustr + " ";
    if (arr.length && (ustr != "")) {
        if (arr.length < 2 || list[0].toLowerCase() != t.toLowerCase()) {
            list.unshift({
                displayText: "Create: " + ustr,
                text: t
            });
        }
    } else {
        if (list.length == 1) {
            list.unshift({
                displayText: "Create: " + ustr,
                text: t
            });
        }
    }
    var o = {
        list: list,
        from: CodeMirror.Pos(cur.line, token.start),
        to: CodeMirror.Pos(cur.line, token.end)
    };
    return o;
}

function sortEList(a, b) {
    return b[1] - a[1];
}
$(document).ready(function(){
	CodeMirror.registerHelper("hint", "logger", getHints);
	CodeMirror.defineMode("logger", function(config, parserConfig) {
		var loggerOverlay = {
			token: function(stream, o) {
				var ch = stream.peek(),
					s = stream.string;
				if (o.error) {
					stream.skipToEnd();
					return "error";
				}
				if (ch == "#" && (stream.pos == 0 || /\s/.test(stream.string.charAt(stream.pos - 1)))) {
					stream.skipToEnd();
					$FORMAT.entry()
					o.erow = true;
					o.hayErow = false;
					return "ENAME";
				}
				if (stream.match(/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>\[\]<\s]+)/, true)) {
					return "YT";
				}
				if (o.erow) {
					var cls;
					for (var i = 0; i < $FORMAT.next.length; i++) {
						if (cls = $FORMAT.next[i].call($FORMAT, stream, o)) {
							if (o.erow) {
								return cls;
							} else {
								break;
							}
						}
					}
					if (!o.erow) {} else {
						o.error = true;
						stream.skipToEnd();
						return 'error';
					}
				}
				stream.next();
				return null;
			},
			startState: function() {
				return {
					erow: 0,
					error: false,
					hayErow: false
				};
			}
		}
		return CodeMirror.overlayMode(CodeMirror.getMode(config, parserConfig.backdrop || "text/html"), loggerOverlay);
	});
	var WxRxS = {
		next: null,
		W: function(s, o) {
			if (s.sol()) {
				if (s.match(/^\s*\d+(?:\.\d+)?(\s*,\s*\d+(?:\.\d+)?)+/i, true)) {
					o.hayErow = true;
					this.next = [this.W, this.WWR, this.C];
					return "W";
				} else if (s.match(/^\s*\d+(\.\d{1,2})?(\s*kg|\s*lbs?)?/i, true) || s.match(/^\s*BW(\s*[\+\-]\s*\d+(\.\d{1,2})?(\s*(kg|lbs?))?)?/i, true)) {
					o.hayErow = true;
					this.next = [this.W, this.RR, this.R, this.C];
					return "W";
				}
				if (o.hayErow) {
					o.erow = null;
				}
			}
		},
		WWR: function(s, o) {
			if (s.match(/^\s*[x×]\s*\d+/, true)) {
				this.next = [this.W, this.C];
				return "R";
			}
		},
		RR: function(s, o) {
			if (s.match(/^\s*[x×]\s*\d+(\s*,\s*\d+)+/, true)) {
				this.next = [this.W, this.C];
				return "RR";
			}
		},
		R: function(s, o) {
			if (s.match(/^\s*[x×]\s*\d+/, true)) {
				this.next = [this.W, this.S, this.C];
				return "R";
			}
		},
		S: function(s, o) {
			if (s.match(/^\s*[x×]\s*[1-9]\d*/, true)) {
				this.next = [this.W, this.C];
				return "S";
			}
		},
		C: function(s, o) {
			if (s.match(/^\s+.*/, true)) {
				this.next = [this.W];
				return "C";
			}
		},
		entry: function(s) {
			this.next = [this.W];
		}
	};
	var $FORMAT = WxRxS;
	CodeMirror.commands.autocomplete = function(cm) {
		cm.showHint({hint: CodeMirror.hint.logger});
	}
	var editor = CodeMirror.fromTextArea(
        $("#log").get(0),
        {
            mode: "logger",
            extraKeys: {"Ctrl": "autocomplete"}
        });
	editor.on("keyup", function(cm, event) {
		//only show hits for alpha characters
		if(!editor.state.completionActive && (event.keyCode > 65 && event.keyCode < 92)) {
			if(timeout) clearTimeout(timeout);
			var timeout = setTimeout(function() {
				CodeMirror.showHint(cm, CodeMirror.hint.logger, {completeSingle: false});
			}, 150);
		}
	});
});
</script>
 <style>
	.cm-ENAME { color:#9900FF ;} 
	.cm-W { color:#1900FF;} 
	.cm-R,.cm-RR { color:#00E5FF;} 
	.cm-S { color:#FF9900;}
	
	.cm-ENAME { color:#3338B7;} 
	.cm-W { color:#337AB7;} 
	.cm-R,.cm-RR { color:#B7337A;} 
	.cm-S { color:#7AB733;}
	.cm-C { color:#191919; font-style: italic; }
	.cm-error{ text-decoration: underline; background:#f00; color:#fff !important; }
	.cm-YT { background: #4C8EFA; color:#fff !important;}
	.CodeMirror {
		height: 500px;
		padding: 6px 12px;
		font-size: 14px;
		line-height: 1.42857143;
		color: #555;
		background-color: #fff;
		background-image: none;
		border: 1px solid #ccc;
		border-radius: 4px;
		-webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
		box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
		-webkit-transition: border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s;
		-o-transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
		transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
	}
</style>

<!-- IF ERROR ne '' -->
<p class="bg-danger">{ERROR}</p>
<!-- ENDIF -->
<h2>Log for {DATE}</h2>
<small><a href="?do=view&page=log&date={DATE}">&larr; Back to log</a></small>

<form action="?page=log&do=edit<!-- IF DATE ne '' -->&date={DATE}<!-- ENDIF -->" method="post">
<div class="form-group">
    <label for="log">Log Data:</label>
	<textarea rows="30" cols="50" name="log" id="log" class="form-control">{LOG}</textarea>
</div>
<label for="weight">Bodyweight:</label>
<div class="input-group">
	<input type="text" class="form-control" placeholder="User's Weight" aria-describedby="basic-addon2" name="weight" value="{WEIGHT}">
	<span class="input-group-addon" id="basic-addon2">kg</span>
</div>
<div class="input-group margintb">
	<input type="hidden" name="csrftoken" value="{_CSRFTOKEN}">
	<input type="submit" name="action" class="btn btn-default" value="<!-- IF VALID_LOG -->Edit<!-- ELSE -->Add<!-- ENDIF --> log">
</div>
</form>