var dwc__Divs = new Array("dwc_git_advanced","dwc_repos_div","dcw_update_git","dcw_db_update","dwc_info_div","dwc_query_div","dwc_prune_div");


function dwc_toggle_div(div) {

    var d = document.getElementById(div);
    if(!d) return;

    if(d.style.display && d.style.display == 'block') {
        d.style.display="none";        
    }
    else {
       d.style.display="block";
       dwc_toggle_msg('block'); 
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

function dwc_close_all() {

    for(var i=0; i<dwc__Divs.length; i++) {
         var d = document.getElementById(dwc__Divs[i]);
         d.style.display = 'none';
    }
   dwc_toggle_msg('block') 
}

/* get the help/info document from the currently displayed working div and put its contents at
   the hash position relevant to the displayed div
*/
function dwc_help(infoid) {

  var state = dwc_toggle_div("dwc_info_div");
  if(state == 'none')dwc_toggle_div("dwc_info_div");
  location = '#'+ infoid; 
}

function msg_area_bigger() {
   var dom = document.getElementById('dwc_msgarea');
   if(!dom.style.height) { 
       dom.style.height = '200px';
       return;
   }
  var h = parseInt(dom.style.height); 
  h+=50;
  dom.style.height = h+'px';

}

function msg_area_smaller() {
  var dom = document.getElementById('dwc_msgarea');
  if(!dom.style.height) {
   dom.style.height = "50px";
   return;
  }
  var h = parseInt(dom.style.height); 
  if(h <=50) return;
  h-=50;
  dom.style.height = h+'px';

}

