// Avatar FOR NAMES 

const colours = ["#1abc9c", "#2ecc71", "#3498db", "#9b59b6", "#34495e", "#16a085", "#27ae60", "#2980b9", "#8e44ad", "#2c3e50", "#f1c40f", "#e67e22", "#e74c3c", "#95a5a6", "#f39c12", "#d35400", "#c0392b", "#bdc3c7", "#7f8c8d"];

const name = "{{ $data['firstName'] }} {{ $data['lastName'] }}",
    nameSplit = name.split(" "),
    initials = nameSplit[0].charAt(0).toUpperCase() + nameSplit[1].charAt(0).toUpperCase();

const charIndex = initials.charCodeAt(0) - 65,
    colourIndex = charIndex % 19;

const canvas = document.getElementById("user-icon");
const context = canvas.getContext("2d");

const canvasWidth = $(canvas).attr("width"),
    canvasHeight = $(canvas).attr("height"),
    canvasCssWidth = canvasWidth,
    canvasCssHeight = canvasHeight;

if (window.devicePixelRatio) {
    $(canvas).attr("width", canvasWidth * window.devicePixelRatio);
    $(canvas).attr("height", canvasHeight * window.devicePixelRatio);
    $(canvas).css("width", canvasCssWidth);
    $(canvas).css("height", canvasCssHeight);
    context.scale(window.devicePixelRatio, window.devicePixelRatio);
}

context.fillStyle = colours[colourIndex];
context.fillRect (0, 0, canvas.width, canvas.height);
context.font = "50px Arial";
context.textAlign = "center";
context.fillStyle = "#FFF";
context.fillText(initials, canvasCssWidth / 2, canvasCssHeight / 1.5);