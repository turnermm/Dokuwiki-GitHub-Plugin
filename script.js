
function dwc_show_extra() {
   var d = document.getElementById('dwc_git_extra_div');
 if(d.style.display && d.style.display == 'block') {
       dwc_hide_extra() 
 }
 else {
     d.style.display="block";
 }
}

function dwc_hide_extra() {
   var d = document.getElementById('dwc_git_extra_div');
   d.style.display="none";

}

function dwc_branch(op) {

var selected = op.options[op.selectedIndex];
op.form['dwc__branch'].value=selected.text;

}
