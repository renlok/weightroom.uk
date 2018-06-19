'use strict';

Date.prototype.dateString = function() {
    var mm = this.getMonth() + 1; // getMonth() is zero-based
    var dd = this.getDate();

    return [this.getFullYear(),
        (mm>9 ? '' : '0') + mm,
        (dd>9 ? '' : '0') + dd
    ].join('-');
};

var app = {
    isLoading: true,
    logDates: [],
    user: '',
    currentDate: (new Date).dateString(),
    spinner: document.querySelector('.loader'),
    container: document.querySelector('.main'),
    dateHeader: document.querySelector('#date'),
    viewLog: document.querySelector('#view-log-wrapper'),
    addLog: document.querySelector('#add-log-wrapper'),
    addLogButton: document.querySelector('#add-log-button'),
    addLogTextarea: document.querySelector('#log'),
    addLogForm: document.querySelector('#log-form'),
    addLogWeight: document.querySelector('#log-weight'),
};


/*****************************************************************************
 *
 * Event listeners for UI elements
 *
 ****************************************************************************/

document.getElementById('add-log-button').addEventListener('click', function() {
    // Open/show the add log form
    app.toggleLog();
    return false;
});

document.getElementById('refresh-log-button').addEventListener('click', function() {
    // reload log
    app.loadLog(new Date(app.currentDate), true);
    return false;
});

app.addLogForm.addEventListener('submit', function(event) {
    event.preventDefault();
    var request = new XMLHttpRequest();
    var url = 'https://weightroom.uk/log/' + app.currentDate + '/' + (app.logVue.is_empty ? 'new' : 'edit');
    request.onreadystatechange = function() {
        if (request.readyState === XMLHttpRequest.DONE) {
            if (request.status === 200) {
                app.isLoading = true;
                app.toggleLoading();
                app.getAPIrequest(url, null, true);
                app.loadLog(new Date(app.currentDate), true);
            }
        } else {
            // Return the initial weather forecast since no data is available.
            //app.updateForecastCard(initialWeatherForecast);
        }
    };
    request.open('POST', url);
    request.send(new FormData(app.addLogForm));
    return false;
});

document.addEventListener('DOMContentLoaded', function() {
    var url = 'https://weightroom.uk/api/v1/cal/'+ app.user;
    app.getAPIrequest(url, app.updateCalDates);
});

document.addEventListener('DOMContentLoaded', function() {
    var elems = document.querySelectorAll('.sidenav');
    M.Sidenav.init(elems, {});
});

var start = null;
window.addEventListener("touchstart",function(event){
    if(event.touches.length === 1){
        //just one finger touched
        start = event.touches.item(0).clientX;
    } else {
        //a second finger hit the screen, abort the touch
        start = null;
    }
});

window.addEventListener("touchend",function(event){
    var offset = 100;//at least 100px are a swipe
    if(start) {
        //the only finger that hit the screen left it
        var end = event.changedTouches.item(0).clientX;
        var date = new Date(app.currentDate)

        if(end > start + offset) {
            date.setDate(date.getDate() - 1);
            app.loadLog(date);
        }
        else if(end < start - offset) {
            date.setDate(date.getDate() + 1);
            app.loadLog(date);
        }
    }
});


/*****************************************************************************
 *
 * Methods for dealing with the model
 *
 ****************************************************************************/

app.toggleLog = function(force) {
    var hidden = app.addLog.hasAttribute('hidden');
    if ((hidden && force != 'view') || force == 'add') {
        app.addLog.removeAttribute('hidden');
        app.viewLog.setAttribute('hidden', 'true');
        app.addLogButton.children[0].innerText = 'close';
        // make sure content is loaded
        editor.reload();
        editor.focus();
    } else {
        app.viewLog.removeAttribute('hidden');
        app.addLog.setAttribute('hidden', 'true');
        app.addLogButton.children[0].innerText = 'add';
    }
};

app.toggleLoading = function() {
    if (app.isLoading) {
        app.spinner.removeAttribute('hidden');
        app.container.setAttribute('hidden', 'true');
    } else {
        app.container.removeAttribute('hidden');
        app.spinner.setAttribute('hidden', 'true');
    }
};

app.getAPIrequest = function(url, func, force) {
    if (!force && 'caches' in window) {
        /*
         * Check if the service worker has already cached this city's weather
         * data. If the service worker has the data, then display the cached
         * data while the app fetches the latest data.
         */
        caches.match(url).then(function(response) {
            if (response) {
                response.json().then(function updateFromCache(json) {
                    func(json);
                });
            } else {
                app.requestURLData(url, func);
            }
        });
    } else {
        app.requestURLData(url, func);
    }
};

app.requestURLData = function(url, func) {
    // Fetch the latest data.
    var request = new XMLHttpRequest();
    request.onreadystatechange = function() {
        if (request.readyState === XMLHttpRequest.DONE) {
            if (request.status === 200 && func != null) {
                var response = JSON.parse(request.response);
                func(response);
            }
        } else {
            // Return the initial weather forecast since no data is available.
            //app.updateForecastCard(initialWeatherForecast);
        }
    };
    request.open('GET', url);
    request.send();
};

app.logVue = new Vue({
    el: '#log-view',
    data: {
        log_data:{"log_id":529,"user_id":1,"log_text":'',"log_date":'2018-02-19',"log_total_volume":4615.3786,"log_total_reps":131,"log_total_sets":47,"log_failed_volume":0,"log_failed_sets":0,"log_warmup_volume":0,"log_warmup_reps":0,"log_warmup_sets":0,"log_total_time":314145,"log_total_distance":0,"log_comment":"this is a comment about the entire workout","log_weight":90,"log_exercises":[{"logex_id":1306,"log_id":529,"user_id":1,"exercise_id":3,"logex_volume":1254.3786,"logex_reps":33,"logex_sets":17,"logex_failed_volume":0,"logex_failed_sets":0,"logex_warmup_volume":0,"logex_warmup_reps":0,"logex_warmup_sets":0,"logex_inol":0.5665494,"logex_inol_warmup":0,"logex_time":0,"logex_distance":0,"logex_comment":"this is a comment about the squats in general","logex_1rm":57.7450475,"logex_order":0,"average_intensity":"36%","log_items":[{"logitem_id":5472,"log_id":529,"logex_id":1306,"user_id":1,"exercise_id":3,"logitem_weight":20,"logitem_time":0,"logitem_distance":0,"logitem_abs_weight":20,"logitem_1rm":21.2142381,"logitem_reps":2,"logitem_sets":3,"logitem_pre":null,"logitem_comment":"this is a comment, this set was 20kg for 2 reps for 3 sets","logitem_order":0,"logex_order":0,"is_bw":false,"is_time":false,"is_pr":false,"is_warmup":false,"is_endurance":false,"is_distance":false,"display_value":20,"show_unit":true}],"exercise":{"exercise_id":3,"exercise_name":"Squat","exercise_name_clean":"Squat","user_id":1,"is_time":false,"is_endurance":false,"is_distance":false,"exercise_update_prs":0}}]},
        is_empty: true
    }
});

app.updateLog = function(logData) {
    logData = logData.log_data[0];
    app.logVue.is_empty = (!logData || logData.length == 0);
    app.logVue.log_data = Object.assign({}, app.logVue.log_data, logData);
    // set and refresh codemirror value
    editor.setValue(logData ? logData.log_text : '');
    editor.refresh();
    app.addLogWeight.value = logData ? logData.log_weight : '';
    app.toggleLog('view');
    app.isLoading = false;
    app.toggleLoading();
};

app.updateCalDates = function(logDates) {
    app.logDates = logDates;
    var elems = document.querySelector('#datepicker');
    app.datePicker = M.Datepicker.init(elems, {
        events: app.logDates,
        onSelect: function (date) {
            this.setDate(Date.parse(date));
            app.loadLog(date);
            this.close();
        }
    });
};

app.loadLog = function(date, force) {
    app.isLoading = true;
    app.toggleLoading();
    app.currentDate = date.dateString();
    app.dateHeader.innerText = date.toDateString();
    var url = 'https://weightroom.uk/api/v1/log/'+ app.user +'/'+ app.currentDate;
    app.getAPIrequest(url, app.updateLog, force);
};

app.round = function(number) {
    number = number * 100;
    number = Math.round(number);
    number = number / 100;
    return number;
};

app.setUsername = function(userData) {
    if (userData.user_name) {
        app.user = userData.user_name;
        // load start up data
        app.loadLog(new Date(app.currentDate));
    } else {
        window.location = 'https://weightroom.uk/login';
    }
};

var url = 'https://weightroom.uk/api/v1/username/';
app.getAPIrequest(url, app.setUsername, true);

if ('serviceWorker' in navigator) {
    navigator.serviceWorker
        .register('../js/serviceworker.js')
        .then(function() { console.log('Service Worker Registered'); });
}
