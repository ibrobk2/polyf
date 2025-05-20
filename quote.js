const btn = document.querySelector('button');

btn.addEventListener('click', function(){
    const quotes = [
        "There is no smoke without fire",
        "There is no shortcut to success",
        "You can either have result or excuse but not both",
        "Boys will be boys",
        "Wherever focus goes energy flows",
        "One with God is majority",
        "Blood is thicker than water",
        "Honesty is the best policy",
        "The hardway is the only way",
        "Knowledge is light",
        "There is no knowledge that is not power",
        "The harder you work, the luckier you become",
        "Like attracts like"
    ];
    let randNum = Math.floor(Math.random()*quotes.length);
    // console.log(randNum);
    // console.log(quotes[randNum]);
    document.querySelector('h2').innerHTML = "<q>"+quotes[randNum]+"</q>";
})