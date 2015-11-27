document.addEventListener("DOMContentLoaded", function(event) {
  document.getElementById("objectFilterCount").textContent 
    = document.getElementById("bolObjectList").options.length;
});

function bolObjectFilter(searchString) {

  var objList = document.getElementById("bolObjectList");

  var active = 0;
  for(var i=0; i < objList.options.length; i++) {
    var elem = objList.options[i];
    if(elem.text.toLowerCase().indexOf(searchString.toLowerCase()) < 0) {
      elem.style.display = 'none';
    } else {
      elem.style.display = 'block';
      active++;
    }
  }
  
  document.getElementById("objectFilterCount").textContent = active;

}
