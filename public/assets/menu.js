function menuToggle() {
    var menu = document.getElementById("myTopnav");
    if (menu.className === "topnav") {
        menu.className += " responsive";
    } else {
        menu.className = "topnav";
    }
}