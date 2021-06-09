const source = new EventSource("/post/getAllPost");
source.onmessage = function(event) {
const data = JSON.parse(event.data)
const inner =` <p> My name is  ${data.name} </p> <p> My email is ${data.email}</p><p> My email is ${data.animal}</p>`
document.getElementById("result").innerHTML = inner + "<br>";

console.log(data)

};