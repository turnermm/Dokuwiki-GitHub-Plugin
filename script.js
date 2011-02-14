

function dwc_toggle_div(div) {
   var d = document.getElementById(div);
  if(d.style.display && d.style.display == 'block') {
        d.style.display="none";
  }
  else {
     d.style.display="block";
 }
  
}

function dwc_branch(op) {

var selected = op.options[op.selectedIndex];
op.form['dwc__branch'].value=selected.text;

}
