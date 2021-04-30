  export const realTimeServer = (input, url, outputId) => {
        let theInput, inputVal, output;
        theInput = this.id(input)
        output = this.id(outputId)
        theInput.addEventListener('keyup', () => {
            inputVal = theInput.value

            if (inputVal == 0) {
                output.innerHTML = "";
                return;
            } else {
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onreadystatechange = function () {
                    if (this.readyState == 4 && this.status == 200) {
                        output.innerHTML = this.responseText;
                    }
                }
                xmlhttp.open("GET", `${url}=${inputVal}`, true);
                xmlhttp.send();
            }
        })
    }

    export const loaderIconBootstrap = () => {

        return `<div class="spinner-grow text-primary" role="status">
        <span class="sr-only">Loading...</span>
        </div>`
    }

    export const loaderIcon = () => {

        return `<div class="loader"></div>`
    }