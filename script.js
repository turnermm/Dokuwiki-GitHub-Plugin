

function dwc_toggle_div(div) {
   var d = document.getElementById(div);
  if(d.style.display && d.style.display == 'block') {
        d.style.display="none";
  }
  else {
     d.style.display="block";
 }
    return d.style.display;
}

function dwc_toggle_msg(which) {
  var dtop = document.getElementById('dwc_msgareatop');
  var dma = document.getElementById('dwc_msgarea');

  dtop.style.display = which;
  dma.style.display = which;
}
function dwc_toggle_info(div) {
  var status = dwc_toggle_div(div);

  if(status == 'block') {
     dwc_toggle_msg('none');    
  }
  else dwc_toggle_msg('block');    
  
}

function dwc_branch(op) {

var selected = op.options[op.selectedIndex];
op.form['dwc__branch'].value=selected.text;


}
function dwc_repro(op) {

var selected = op.options[op.selectedIndex];
op.form['dwc__repro'].value=selected.text;


}
