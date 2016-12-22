var intro = introJs();
intro.setOption("skipLabel", "Exit");

intro.addStep({
    element: document.querySelectorAll('#track_header')[0],
    intro: "Welcome to the workout logger. This is a quick guide as to how it works",
    position: 'bottom'
});

intro.addStep({
    element: document.querySelectorAll('#track_date')[0],
    intro: "To change the date for this log use the date picker",
    position: 'bottom'
});

intro.addStep({
    element: document.querySelectorAll('#log-box')[0],
    intro: "This is where you will enter your workout",
    position: 'top'
});

intro.addStep({
    element: document.querySelectorAll('#log-box')[0],
    intro: "To add an exercise to the log, on an empty line, enter hashtag followed by the name of the exercise",
    position: 'auto'
});

intro.addStep({
    element: document.querySelectorAll('#log-box')[0],
    intro: "After the exercises name, on the next empty line, add your sets (weight x reps x sets) or time or distance",
    position: 'top'
});

intro.addStep({
    element: document.querySelectorAll('#log-box')[0],
    intro: "A simple example<br><code>#bro curl</pre><br>20 kg x 8 x 5",
    position: 'top'
});

intro.addStep({
    element: document.querySelectorAll('#log-box')[0],
    intro: "Or maybe<br><code>#cycle</pre><br>50 km",
    position: 'top'
});

intro.addStep({
    element: document.querySelectorAll('#openhelp')[0],
    intro: "For more detailed formatting help click here",
    position: 'top'
});

intro.addStep({
    element: document.querySelectorAll('#log-submit')[0],
    intro: "Once done submit your log, you can always make changes later",
    position: 'top'
});

intro.start();
