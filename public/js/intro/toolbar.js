$(document).ready(function(){
    var intro = introJs();
    intro.setOption("skipLabel", "Exit");

    intro.addStep({
        element: document.querySelectorAll('#home-nav')[0],
        intro: "Where you can see logs that have recently been posted",
        position: 'bottom'
    });

    intro.addStep({
        element: document.querySelectorAll('#track-nav')[0],
        intro: "Add your workout to the tracker",
        position: 'bottom'
    });

    intro.addStep({
        element: document.querySelectorAll('#view-nav')[0],
        intro: "View your past workouts",
        position: 'bottom'
    });

    intro.addStep({
        element: document.querySelectorAll('#exercise-nav')[0],
        intro: "After you add workouts the exercises you track will appear here",
        position: 'bottom'
    });

    intro.addStep({
        element: document.querySelectorAll('#tools-nav')[0],
        intro: "Here's where you will find the different analytical graphs and calculators. Also where you can find workout templates and import/export functions.",
        position: 'bottom'
    });

    intro.addStep({
        element: document.querySelectorAll('#search-nav')[0],
        intro: "Search for users to follow",
        position: 'bottom'
    });

    intro.start();
});
