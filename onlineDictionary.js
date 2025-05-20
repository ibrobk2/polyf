// alert("Confused!")
 const btn = document.querySelector('button');
const dict = {
   input: document.getElementById('in'),
   define: function(){
    fetch('https://api.dictionaryapi.dev/api/v2/entries/en/'+this.input.value)
    .then((res)=>res.json())
    .then(data=>{
        const definitions = data[0].meanings[0].definitions;
        const def = definitions.map((x)=>"Definition: "+ x.definition+"<br>")
        document.getElementById('result').innerHTML = def;
        // console.log(def);
        // console.log(data[0].meanings[0].definitions)
        // console.log(data)
    })
   },


}



btn.addEventListener('click', ()=>{
    dict.define()
})
