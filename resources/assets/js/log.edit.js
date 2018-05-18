$('#openhelp').click(function() {
    if ($("#formattinghelp").val() == '')
    {
        // load text
        CodeMirror.runMode(
            $('#formattinghelptext').val(),
            "logger",
            $("#formattinghelp").get(0)
        );
    }
    $('#formattinghelp').slideToggle('fast');
    return false;
});

function getHints(cm) {
    var cur = cm.getCursor(),
        token = cm.getTokenAt(cur),
        str = token.string,
        ustr = $.trim(token.string.substr(1)),
        arr = [],
        list = [],
        searchList = $ELIST;
    var plusStr = '#';
    if (str.indexOf("#") !== 0) {
        return null;
    }
    const stringParts = str.split("#");
    if (stringParts.length > 2) {
        ustr = stringParts[stringParts.length - 1];
        plusStr = str.slice(0, (ustr.length * -1));
        searchList = $GLIST;
    }
    for (var i = 0; i < searchList.length; i++) {
        if ((ustr == "") || searchList[i][0].toLowerCase().indexOf(ustr.toLowerCase()) > -1) {
            arr.push(searchList[i]);
        }
    }
    arr.sort();
    for (i = 0; i < arr.length; i++) {
        list.push({
            displayText: "#" + arr[i][0],
            text: plusStr + arr[i][0] + " "
        });
    }
    var t = plusStr + ustr + " ";
    if (arr.length && (ustr != "")) {
        if (arr.length < 2 || list[0].displayText.toLowerCase() != ('#'+ustr).toLowerCase()) {
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
    const hints = {
        list: list,
        from: CodeMirror.Pos(cur.line, token.start),
        to: CodeMirror.Pos(cur.line, token.end)
    };
    return hints;
}

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
    WW: function(s, o) {
        if (s.match(/^\s*\d+(\s*:\s*\d{1,2}){1,2}(\s*,\s*)?/i, true) ||
            s.match(/^\s*\d+(\.\d*)?\s*(second|sec|minute|min|hour|hr)s?(\s*,\s*)?/i, true) ||
            s.match(/^\s*\d+(\.\d*)?\s*(mile|m|km)s?(\s*,\s*)?/i, true) ||
            s.match(/^\s*\d+(\.\d*)?(\s*kgs?|\s*lbs?)?(\s*,\s*)?/i, true) ||
            s.match(/^\s*BW(\s*[\+\-]\s*\d+(\.\d{1,2})?(\s*(kgs?|lbs?))?(\s*,\s*)?)?/i, true)) {
            this.next = [this.W, this.WW, this.RR, this.R, this.RPERPE, this.RPE, this.C];
            return "WW";
        }
    },
    W: function(s, o) {
        if (s.sol()) {
            if (s.match(/^\s*\d+(\s*:\s*\d{1,2}){1,2}(\s*,\s*)?/i, true) ||
                s.match(/^\s*\d+(\.\d*)?\s*(second|sec|minute|min|hour|hr)s?(\s*,\s*)?/i, true) ||
                s.match(/^\s*\d+(\.\d*)?\s*(mile|m|km)s?(\s*,\s*)?/i, true) ||
                s.match(/^\s*\d+(\.\d*)?(\s*kgs?|\s*lbs?)?(\s*,\s*)?/i, true) ||
                s.match(/^\s*BW(\s*[\+\-]\s*\d+(\.\d{1,2})?(\s*(kgs?|lbs?))?(\s*,\s*)?)?/i, true)) {
                o.hayErow = true;
                this.next = [this.W, this.WW, this.RR, this.R, this.RPERPE, this.RPE, this.C];
                return "W";
            }
            if (o.hayErow) {
                o.erow = null;
            }
        }
    },
    RR: function(s, o) {
        if (s.match(/^\s*[x×*]\s*\d+(\s*,\s*\d+)+/, true)) {
            this.next = [this.W, this.SS, this.S, this.RPERPE, this.RPE, this.C];
            return "RR";
        }
    },
    R: function(s, o) {
        if (s.match(/^\s*[x×*]\s*\d+/, true)) {
            this.next = [this.W, this.SS, this.S, this.RPERPE, this.RPE, this.C];
            return "R";
        }
    },
    SS: function(s, o) {
        if (s.match(/^\s*[x×*]\s*[1-9]\d*(\s*,\s*[1-9]\d*)+/, true)) {
            this.next = [this.W, this.RPERPE, this.RPE, this.C];
            return "SS";
        }
    },
    S: function(s, o) {
        if (s.match(/^\s*[x×*]\s*[1-9]\d*/, true)) {
            this.next = [this.W, this.RPERPE, this.RPE, this.C];
            return "S";
        }
    },
    RPERPE: function(s, o) {
        if (s.match(/^\s*[@]\s*(10|[0-9](\.\d)?)(\s*,\s*(10|[0-9](\.\d)?))+/, true)) {
            this.next = [this.W, this.C];
            return "RPERPE";
        }
    },
    RPE: function(s, o) {
        if (s.match(/^\s*[@]\s*(10|[0-9](\.\d)?)/, true)) {
            this.next = [this.W, this.C];
            return "RPE";
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
        lineWrapping: true,
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