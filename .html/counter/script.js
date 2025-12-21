let countEl = document.getElementById('count-el');

let count = 0;

function increment() {
    count = count + 1;
    countEl.innerText = count;
}

function decrement() {
    if (count > 0){
         count = count - 1;
    }
    else {
        count = 0;
        alert ("Count cannot be negative");
    }
   
    countEl.innerText = count;
}   

function reset() {
    count = 0;
    countEl.innerText = count;
}
