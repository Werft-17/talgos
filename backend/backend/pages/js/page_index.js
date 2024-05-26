/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          Website Baker Project, LEPTON Project
 * @copyright       2004-2010 Website Baker Project
 * @copyright       2010-2024 LEPTON Project
 * @link            https://lepton-cms.org
 * @license         https://gnu.org/licenses/gpl-3.0.html
 * @license_terms   please see LICENSE and COPYING files in your package
 *
 */

 /*-- Addition for pages_settings --*/
 function toggle_viewers() {
	if(document.settings.visibility.value == 'private' || document.settings.visibility.value == 'registered') {
		document.getElementById('allowed_viewers').style.display = 'block';
	} else {
		document.getElementById('allowed_viewers').style.display = 'none';
	}
}
var lastselectedindex = new Array();

function disabled_hack_for_ie(sel) {
	var sels = document.getElementsByTagName("select");
	var i;
	var sel_num_in_doc = 0;
	for (i = 0; i <sels.length; i++) {
		if (sel == sels[i]) {
			sel_num_in_doc = i;
		}
	}
	// never true for browsers that support option.disabled
	if (sel.options[sel.selectedIndex].disabled) {
		sel.selectedIndex = lastselectedindex[sel_num_in_doc];
	} else {
		lastselectedindex[sel_num_in_doc] = sel.selectedIndex;
	}
	return true;
}
/*-- Addition for remembering expanded state of pages --*/
function writeSessionCookie (cookieName, cookieValue) {
	document.cookie = escape(cookieName) + "=" + escape(cookieValue) + ";";
}

function toggle_viewers() {
	if(document.add.visibility.value == 'private') {
		document.getElementById('viewers').style.display = 'block';
	} else if(document.add.visibility.value == 'registered') {
		document.getElementById('viewers').style.display = 'block';
	} else {
		document.getElementById('viewers').style.display = 'none';
	}
}

// [1]
function toggle_visibility(id){
    var data = document.querySelectorAll("[data-group="+id+"]");
    if(data.length > 0) {
        var visi = data[0].style.display == "block" ? "none" : "block";
        for (let i=0; i<data.length; i++) {
            // console.log ("child="+data[i].id);
            data[i].style.display = visi;
        }
        writeSessionCookie(id, (visi == "block" ? "1" : "0") ); //Addition for remembering expanded state of pages
    } else {
        console.log("Array ist leer!");
    }
}

var plus = new Image;
plus.src = LEPTON_URL+"/modules/lib_lepton/backend_images/plus_16.png";
var minus = new Image;
minus.src = LEPTON_URL+"/modules/lib_lepton/backend_images/minus_16.png";
function toggle_plus_minus(id) {
	var img_src = document.images['plus_minus_' + id].src;
	if(img_src == plus.src) {
		document.images['plus_minus_' + id].src = minus.src;
	} else {
		document.images['plus_minus_' + id].src = plus.src;
	}
}

function add_child_page(page_id) {
	//find and select the page in the parent dropdown
	var selectBox = document.add.parent;
	var max = selectBox.options.length;
	for (var i = 0; i < max; i++) {
		if (selectBox.options[i].value == page_id) {
			selectBox.selectedIndex = i;
			break;
		}
	}
	//set focus to add form
	document.add.title.focus();
}