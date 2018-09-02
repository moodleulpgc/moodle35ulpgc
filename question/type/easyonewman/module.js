M.qtype_easyonewman = {
    insert_easyonewman_applet: function(Y, topnode, feedback, readonly, stripped_answer_id, slot) {
        var inputdiv = Y.one(topnode);
        inputdiv.ancestor('form').on('submit', function() {
            var pos0 = document.getElementById('apos0' + slot).value;
            var pos1 = document.getElementById('apos1' + slot).value;
            var pos2 = document.getElementById('apos2' + slot).value;
            var pos3 = document.getElementById('apos3' + slot).value;
            var pos4 = document.getElementById('apos4' + slot).value;
            var pos5 = document.getElementById('apos5' + slot).value;
            textfieldid = topnode + ' input.answer';
            orderstring = pos0 + "-" + pos1 + "-" + pos2 + "-" + pos3 + "-" + pos4 + "-" + pos5;
            Y.one(topnode + ' input.answer').set('value', orderstring);
            Y.one(topnode + ' input.mol').set('value', orderstring);
        }, this);
    },
    insert_structure_into_applet: function(Y, stagoreclip) {
        var textfieldid = 'id_answer_0';
        if (document.getElementById(textfieldid).value != '') {
            var idhand = 'pos';
            if (stagoreclip == '1') {
                idhand = 'epos';
            }
            var s = document.getElementById(textfieldid).value;
            var groups = s.split("-");
            for (var i = 0; i < 6; i++) {
                var elem = document.createElement("img");
                group = groups[i];
                trimgroup = group.substring(0, group.length - 1);
                elem.setAttribute("src", "type/easyonewman/pix/" + trimgroup + ".png");
                elem.setAttribute("id", group + i);
                elem.setAttribute("height", "30");
                elem.setAttribute("width", "40");
                document.getElementById(idhand + i).appendChild(elem);
                document.getElementById("apos" + i).value = group;
            }
        }
    }
}
M.qtype_easyonewman2 = {
    insert_structure_into_applet: function(Y, slot, moodleroot, stagoreclip) {
        var textfieldid = 'my_answer' + slot;
        if (document.getElementById(textfieldid).value != '') {
            var s = document.getElementById(textfieldid).value;
            var groups = s.split("-");
            var idhand = 'pos';
            if (stagoreclip === '1') {
                idhand = 'epos';
            }
            for (var i = 0; i < 6; i++) {
                //document.write(cars[i] + "<br>");
                var elem = document.createElement("img");
                group = groups[i];
                trimgroup = group.substring(0, group.length - 1);
                elem.setAttribute("src", moodleroot + "/question/type/easyonewman/pix/" + trimgroup + ".png");
                elem.setAttribute("id", group);
                elem.setAttribute("height", "30");
                elem.setAttribute("width", "40");
                document.getElementById(idhand + i + slot).appendChild(elem);
                document.getElementById("apos" + i + slot).value = group;
            }
        }
    }
}
M.qtype_easyonewman.init_showmyresponse = function(Y, moodle_version, slot, moodleroot, stagoreclip) {
    var handleSuccess = function(o) {};
    var handleFailure = function(o) { /*failure handler code*/
        };
    var callback = {
        success: handleSuccess,
        failure: handleFailure
    };
    if (moodle_version >= 2012120300) { //Moodle 2.4 or higher
        YAHOO = Y.YUI2;
    }
    var idhand = 'pos';
    if (stagoreclip === '1') {
        idhand = 'epos';
    }
    var refreshBut = Y.one("#myresponse" + slot, slot);
    refreshBut.on("click", function() {
        var textfieldid = 'my_answer1';
        if (document.getElementById(textfieldid).value != '') {
            var s = document.getElementById(textfieldid).value;
            var groups = s.split("-");
            for (var i = 0; i < 6; i++) {
                var elem = document.createElement("img");
                div = document.getElementById(idhand + i + slot);
                div.innerHTML = '';
                group = groups[i];
                trimgroup = group.substring(0, group.length - 1);
                elem.setAttribute("src", moodleroot + "/question/type/easyonewman/pix/" + trimgroup + ".png");
                elem.setAttribute("id", group);
                elem.setAttribute("height", "30");
                elem.setAttribute("width", "40");
                document.getElementById(idhand + i + slot).appendChild(elem);
                document.getElementById("apos" + i + slot).value = group;
            }
        }
    });
};
M.qtype_easyonewman.init_showcorrectanswer = function(Y, moodle_version, slot, moodleroot, stagoreclip) {
    var handleSuccess = function(o) {};
    var handleFailure = function(o) { /*failure handler code*/
        };
    var callback = {
        success: handleSuccess,
        failure: handleFailure
    };
    if (moodle_version >= 2012120300) { //Moodle 2.4 or higher
        YAHOO = Y.YUI2;
    }
    var idhand = 'pos';
    if (stagoreclip === '1') {
        idhand = 'epos';
    }
    var refreshBut = Y.one("#correctanswer" + slot, slot);
    refreshBut.on("click", function() {
        var textfieldid = 'correct_answer' + slot;
        if (document.getElementById(textfieldid).value != '') {
            var s = document.getElementById(textfieldid).value;
            var groups = s.split("-");
            for (var i = 0; i < 6; i++) {
                var elem = document.createElement("img");
                //delete existing image
                div = document.getElementById(idhand + i + slot);
                div.innerHTML = '';
                group = groups[i];
                trimgroup = group.substring(0, group.length - 1);
                elem.setAttribute("src", moodleroot + "/question/type/easyonewman/pix/" + trimgroup + ".png");
                elem.setAttribute("id", group);
                elem.setAttribute("height", "30");
                elem.setAttribute("width", "40");
                document.getElementById(idhand + i + slot).appendChild(elem);
                document.getElementById("apos" + i + slot).value = group;
            }
        }
    });
};
M.qtype_easyonewman.dragndrop = function(Y, slot) {
    YUI().use('dd-drag', 'dd-constrain', 'dd-proxy', 'dd-drop', function(Y) {
        //Listen for all drag:drag events
        Y.DD.DDM.on('drag:drag', function(e) {
            //Get the last y point
            var y = e.target.lastXY[1];
            //is it greater than the lastY var?
            if (y < lastY) {
                //We are going up
                goingUp = true;
            } else {
                //We are going down.
                goingUp = false;
            }
            //Cache for next check
            lastY = y;
        });
        //Listen for all drag:start events
        Y.DD.DDM.on('drag:start', function(e) {
            //Get our drag object
            var drag = e.target;
            //Set some styles here
            drag.get('node').setStyle('opacity', '.25');
            drag.get('dragNode').set('innerHTML', drag.get('node').get('innerHTML'));
            drag.get('dragNode').setStyles({
                opacity: '.5',
                borderColor: drag.get('node').getStyle('borderColor'),
                backgroundColor: drag.get('node').getStyle('backgroundColor')
            });
        });
        //Listen for a drag:end events
        Y.DD.DDM.on('drag:end', function(e) {
            var drag = e.target;
            drag.get('node').setStyles({
                visibility: '',
                opacity: '1'
            });
        });
        Y.DD.DDM.on('drop:hit', function(e) {
            var drop = e.drop.get('node'),
                drag = e.drag.get('node');
            var flag = false;
        });
        //Listen for all drag:drophit events
        Y.DD.DDM.on('drag:drophit', function(e) {
            var drop = e.drop.get('node'),
                drag = e.drag.get('node');
            drop.get('childNodes').remove();
            drop.appendChild(drag);
            var idhand = drop.get('id');
            if (idhand.charAt(0) === 'e') {
                var idhand = idhand.substr(1);
            }
            document.getElementById('a' + idhand).value = drag.get('id');
        });
        //Static Vars
        var goingUp = false,
            lastY = 0;
        var nextsibling = '';
        var dragparentid = '';
        //Get the list of img's and make them draggable
        var lis = Y.Node.all('.dragableimg');
        lis.each(function(v, k) {
            var dd = new Y.DD.Drag({
                node: v,
                target: {
                    padding: '0 0 0 20'
                }
            }).plug(Y.Plugin.DDProxy, {
                moveOnEnd: false,
                cloneNode: true,
            }).plug(Y.Plugin.DDConstrained, {});
        });
        var uls = Y.Node.all('.dropablediv');
        uls.each(function(v, k) {
            var tar = new Y.DD.Drop({
                node: v
            });
        });
    });
};

M.qtype_easyonewman.init_reload = function(Y, url, htmlid) {
    var handleSuccess = function(o) {
            newman_template.innerHTML = o.responseText;
            M.qtype_easyonewman.insert_structure_into_applet(Y, document.getElementById('id_stagoreclip').value);
            //div.innerHTML = "<li>JARL!!!</li>";
        }
    var handleFailure = function(o) { /*failure handler code*/
        }
    var callback = {
        success: handleSuccess,
        failure: handleFailure
    }
    var button = Y.one("#id_stagoreclip");
    button.on("change", function(e) {
        div = Y.YUI2.util.Dom.get(htmlid);
        Y.use('yui2-connection', function(Y) {
            newurl = url + document.getElementById('id_stagoreclip').value;
            Y.YUI2.util.Connect.asyncRequest('GET', newurl, callback);
        });
    });
};


M.qtype_easyonewman.init_getanswerstring = function(Y, stagoreclip) {
    var handleSuccess = function(o) {};
    var handleFailure = function(o) { /*failure handler code*/
        };
    var callback = {
        success: handleSuccess,
        failure: handleFailure
    };
    Y.all(".id_insert").each(function(node) {
        node.on("click", function() {
            var idhand = 'pos';
            if (stagoreclip === '1') {
                idhand = 'epos';
            }
            var pos0 = document.getElementById('apos0').value;
            var pos1 = document.getElementById('apos1').value;
            var pos2 = document.getElementById('apos2').value;
            var pos3 = document.getElementById('apos3').value;
            var pos4 = document.getElementById('apos4').value;
            var pos5 = document.getElementById('apos5').value;
            pos0 = pos0.substring(0, pos0.length - 1) + '6';
            pos1 = pos1.substring(0, pos1.length - 1) + '6';
            pos2 = pos2.substring(0, pos2.length - 1) + '6';
            pos3 = pos3.substring(0, pos3.length - 1) + '6';
            pos4 = pos4.substring(0, pos4.length - 1) + '6';
            pos5 = pos5.substring(0, pos5.length - 1) + '6';
            var buttonid = node.getAttribute("id");
            textfieldid = 'id_answer_' + buttonid.substr(buttonid.length - 1);
            document.getElementById(textfieldid).value = pos0 + "-" + pos1 + "-" + pos2 + "-" + pos3 + "-" + pos4 + "-" + pos5;
        });
    });
};
