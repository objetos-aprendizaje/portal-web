var cursoButton = document.getElementById('cursoButton');
var programaButton = document.getElementById('programaButton');
var recursoButton = document.getElementById('recursoButton');

var cursoContainer = document.querySelector('.cursos-container');
var programaContainer = document.querySelector('.programas-container');
var recursoContainer = document.querySelector('.recursos-container');

cursoButton.addEventListener('click', function() {

  var hijos = this.children;

  for (var i = 0; i < hijos.length; i++) {
    hijos[i].checked = !hijos[i].checked
    
    if(hijos[i].checked== true) {
      cursoContainer.style.display = 'block'
    }
    else if(hijos[i].checked == false) {
      cursoContainer.style.display = 'none'
    }
  }

});

programaButton.addEventListener('click', function() {

  var hijos = this.children;

  for (var i = 0; i < hijos.length; i++) {
    hijos[i].checked = !hijos[i].checked
    
    if(hijos[i].checked== true) {
      programaContainer.style.display = 'block'
    }
    else if(hijos[i].checked == false) {
      programaContainer.style.display = 'none'
    }
  }

});

recursoButton.addEventListener('click', function() {

  var hijos = this.children;

  for (var i = 0; i < hijos.length; i++) {
    hijos[i].checked = !hijos[i].checked

    if(hijos[i].checked== true) {
      recursoContainer.style.display = 'block'
    }
    else if(hijos[i].checked == false) {
      recursoContainer.style.display = 'none'
    }
  }

});

