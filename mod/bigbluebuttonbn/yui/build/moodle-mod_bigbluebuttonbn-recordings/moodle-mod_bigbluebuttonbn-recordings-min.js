YUI.add("moodle-mod_bigbluebuttonbn-recordings",function(e,t){M.mod_bigbluebuttonbn=M.mod_bigbluebuttonbn||{},M.mod_bigbluebuttonbn.recordings={datasource:null,locale:"en",datatable:{},init:function(t){this.datasource=new e.DataSource.Get({source:M.cfg.wwwroot+"/mod/bigbluebuttonbn/bbb_broker.php?"}),t.recordings_html===!1&&(t.profile_features.indexOf("all")!=-1||t.profile_features.indexOf("showrecordings")!=-1)&&(this.locale=t.locale,this.datatable.columns=t.columns,this.datatable.data=this.datatableInitFormatDates(t.data),this.datatableInit()),M.mod_bigbluebuttonbn.helpers.init()},datatableInitFormatDates:function(e){for(var t=0;t<e.length;t++){var n=new Date(e[t].date);e[t].date=n.toLocaleDateString(this.locale,{weekday:"long",year:"numeric",month:"long",day:"numeric"})}return e},datatableInit:function(){var e=this.datatable.columns,t=this.datatable.data;YUI({lang:this.locale}).use("datatable","datatable-sort","datatable-paginator","datatype-number",function(n){var r=(new n.DataTable({width:"1195px",columns:e,data:t,rowsPerPage:10,paginatorLocation:["header","footer"]})).render("#bigbluebuttonbn_recordings_table");return r})},recordingElementPayload:function(t){var n=e.one(t),r=n.ancestor("div");return{action:n.getAttribute("data-action"),recordingid:r.getAttribute("data-recordingid"),meetingid:r.getAttribute("data-meetingid")}},recordingAction:function(e,t,n){var r=this.recordingElementPayload(e);for(var i in n)r[i]=n[i];if(!t){this.recordingActionPerform(r);return}var s=new M.core.confirm({modal:!0,centered:!0,question:this.recordingConfirmationMessage(r)});s.on("complete-yes",function(){this.recordingActionPerform(r)},this)},recordingActionPerform:function(e){M.mod_bigbluebuttonbn.helpers.toggleSpinningWheelOn(e),M.mod_bigbluebuttonbn.broker.recordingActionPerform(e)},recordingPublish:function(e){var t={source:"published",goalstate:"true"};this.recordingAction(e,!1,t)},recordingUnpublish:function(e){var t={source:"published",goalstate:"false"};this.recordingAction(e,!1,t)},recordingProtect:function(e){var t={source:"protected",goalstate:"true"};this.recordingAction(e,!1,t)},recordingUnprotect:function(e){var t={source:"protected",goalstate:"false"};this.recordingAction(e,!1,t)},recordingDelete:function(e){var t={source:"found",goalstate:!1},n=!0;this.recordingIsImported(e)&&(n=!1,t.source="status",t.goalstate=!0,t.attempts=1),this.recordingAction(e,n,t)},recordingImport:function(e){var t={};this.recordingAction(e,!0,t)},recordingUpdate:function(t){var n=e.one(t),r=n.ancestor("div"),i={target:r.getAttribute("data-target"),source:r.getAttribute("data-source"),goalstate:n.getAttribute("data-goalstate")};this.recordingAction(t,!1,i)},recordingEdit:function(t){var n=e.one(t),r=n.ancestor("div"),i=r.one("> span");i.hide(),n.hide();var s=e.Node.create('<input type="text" class="form-control"></input>');s.setAttribute("id",n.getAttribute("id")),s.setAttribute("value",i.getHTML()),s.setAttribute("data-value",i.getHTML()),s.setAttribute("onkeydown","M.mod_bigbluebuttonbn.recordings.recordingEditKeydown(this);"),s.setAttribute("onfocusout","M.mod_bigbluebuttonbn.recordings.recordingEditOnfocusout(this);"),r.append(s)},recordingEditKeydown:function(e){if(event.keyCode==13){this.recordingEditPerform(e);return}event.keyCode==27&&this.recordingEditOnfocusout(e)},recordingEditOnfocusout:function(t){var n=e.one(t),r=n.ancestor("div");n.hide(),r.one("> span").show(),r.one("> a").show()},recordingEditPerform:function(t){var n=e.one(t),r=n.ancestor("div"),i=t.value;n.setAttribute("data-action","edit"),n.setAttribute("data-goalstate",i),M.mod_bigbluebuttonbn.recordings.recordingUpdate(n.getDOMNode()),r.one("> span").setHTML(i);var s=r.one("> a");s.show(),s.focus()},recordingEditCompletion:function(t,n){var r=M.mod_bigbluebuttonbn.helpers.elementId(t.action,t.target),i=e.one("a#"+r+"-"+t.recordingid),s=i.ancestor("div"),o=s.one("> span");if(typeof o=="undefined")return;var u=s.one("> input");n&&o.setHTML(u.getAttribute("data-value")),u.remove()},recordingPlay:function(t){var n=e.one(t),r={target:n.getAttribute("data-target"),source:"published",goalstate:"true",attempts:1,dataset:n.getData()};this.recordingAction(t,!1,r)},recordingConfirmationMessage:function(t){var n,r,i,s,o;return n=M.util.get_string("view_recording_"+t.action+"_confirmation","bigbluebuttonbn"),typeof n=="undefined"?"":(r=M.util.get_string("view_recording","bigbluebuttonbn"),e.one("#playbacks-"+t.recordingid).get("dataset").imported==="true"&&(r=M.util.get_string("view_recording_link","bigbluebuttonbn")),n=n.replace("{$a}",r),t.action==="import"?n:(i=M.mod_bigbluebuttonbn.helpers.elementId(t.action,t.target),s=e.one("a#"+i+"-"+t.recordingid).get("dataset").links,s===0?n:(o=M.util.get_string("view_recording_"+t.action+"_confirmation_warning_p","bigbluebuttonbn"),s==1&&(o=M.util.get_string("view_recording_"+t.action+"_confirmation_warning_s","bigbluebuttonbn")),o=o.replace("{$a}",s)+". ",o+"\n\n"+n)))},recordingActionCompletion:function(t){var n,r,i;if(t.action=="delete"){i=e.one("div#recording-actionbar-"+t.recordingid).ancestor("td").ancestor("tr"),r=i.ancestor("tbody");if(r.all("tr").size()==1){n=e.one("#bigbluebuttonbn_view_recordings_content"),n.prepend("<span>"+M.util.get_string("view_message_norecordings","bigbluebuttonbn")+"</span>"),n.one("#bigbluebuttonbn_recordings_table").remove();return}i.remove();return}if(t.action=="import"){i=e.one("div#recording-actionbar-"+t.recordingid).ancestor("td").ancestor("tr"),i.remove();return}if(t.action=="play"){M.mod_bigbluebuttonbn.helpers.toggleSpinningWheelOff(t),window.open(t.dataset.href,"_self");return}M.mod_bigbluebuttonbn.helpers.updateData(t),M.mod_bigbluebuttonbn.helpers.toggleSpinningWheelOff(t),M.mod_bigbluebuttonbn.helpers.updateId(t),(t.action==="publish"||t.action==="unpublish")&&this.recordingPublishUnpublishCompletion(t)},recordingActionFailover:function(e){var t=new M.core.alert({title:M.util.get_string("error","moodle"),message:e.message});t.show(),M.mod_bigbluebuttonbn.helpers
.toggleSpinningWheelOff(e),e.action==="edit"&&this.recordingEditCompletion(e,!0)},recordingPublishUnpublishCompletion:function(t){var n,r;n=e.one("#playbacks-"+t.recordingid),r=e.one("#preview-"+t.recordingid);if(t.action=="unpublish"){n.hide(),r.hide();return}n.show(),r.show(),M.mod_bigbluebuttonbn.helpers.reloadPreview(t)},recordingIsImported:function(t){var n=e.one(t),r=n.ancestor("tr");return r.getAttribute("data-imported")==="true"}},M.mod_bigbluebuttonbn=M.mod_bigbluebuttonbn||{},M.mod_bigbluebuttonbn.helpers={elementTag:{},elementFaClass:{},elementActionReversed:{},init:function(){this.elementTag=this.initElementTag(),this.elementFaClass=this.initElementFAClass(),this.elementActionReversed=this.initElementActionReversed()},toggleSpinningWheelOn:function(t){var n,r,i,s;n=this.elementId(t.action,t.target),s=M.util.get_string("view_recording_list_action_"+t.action,"bigbluebuttonbn"),r=e.one("a#"+n+"-"+t.recordingid),r.setAttribute("data-onclick",r.getAttribute("onclick")),r.setAttribute("onclick",""),i=r.one("> i");if(i===null){this.toggleSpinningWheelOnCompatible(r,s);return}i.setAttribute("data-aria-label",i.getAttribute("aria-label")),i.setAttribute("aria-label",s),i.setAttribute("data-title",i.getAttribute("title")),i.setAttribute("title",s),i.setAttribute("data-class",i.getAttribute("class")),i.setAttribute("class",this.elementFaClass.process)},toggleSpinningWheelOnCompatible:function(e,t){var n=e.one("> img");if(n===null)return;n.setAttribute("data-alt",n.getAttribute("alt")),n.setAttribute("alt",t),n.setAttribute("data-title",n.getAttribute("title")),n.setAttribute("title",t),n.setAttribute("data-src",n.getAttribute("src")),n.setAttribute("src","pix/i/processing16.gif")},toggleSpinningWheelOff:function(t){var n,r,i;n=this.elementId(t.action,t.target),r=e.one("a#"+n+"-"+t.recordingid),r.setAttribute("onclick",r.getAttribute("data-onclick")),r.removeAttribute("data-onclick"),i=r.one("> i");if(i===null){this.toggleSpinningWheelOffCompatible(r);return}i.setAttribute("aria-label",i.getAttribute("data-aria-label")),i.removeAttribute("data-aria-label"),i.setAttribute("title",i.getAttribute("data-title")),i.removeAttribute("data-title"),i.setAttribute("class",i.getAttribute("data-class")),i.removeAttribute("data-class")},toggleSpinningWheelOffCompatible:function(e){var t=e.one("> img");if(t===null)return;t.setAttribute("alt",t.getAttribute("data-alt")),t.removeAttribute("data-alt"),t.setAttribute("title",t.getAttribute("data-title")),t.removeAttribute("data-title"),t.setAttribute("src",t.getAttribute("data-src")),t.removeAttribute("data-src")},updateData:function(t){var n,r,i,s,o,u,a;n=this.elementActionReversed[t.action];if(n===t.action)return;r=this.elementId(t.action,t.target),i=e.one("a#"+r+"-"+t.recordingid),i.setAttribute("data-action",n),s=i.getAttribute("data-onclick").replace(this.capitalize(t.action),this.capitalize(n)),i.setAttribute("data-onclick",s),u=M.util.get_string("view_recording_list_actionbar_"+n,"bigbluebuttonbn"),a=this.elementTag[n],o=i.one("> i");if(o===null){this.updateDataCompatible(i.one("> img"),this.elementTag[t.action],a,u);return}o.setAttribute("data-aria-label",u),o.setAttribute("data-title",u),o.setAttribute("data-class",this.elementFaClass[n])},updateDataCompatible:function(e,t,n,r){var i;i=e.getAttribute("data-src").replace(this.capitalize(t),this.capitalize(n)),e.setAttribute("data-alt",r),e.setAttribute("data-title",r),e.setAttribute("data-src",i)},updateId:function(t){var n,r,i,s,o;n=this.elementActionReversed[t.action];if(n===t.action)return;r=this.elementId(t.action,t.target),i=e.one("a#"+r+"-"+t.recordingid),o=""+r.replace(t.action,n)+"-"+t.recordingid,i.setAttribute("id",o),s=i.one("> i"),s===null&&(s=i.one("> img")),s.removeAttribute("id")},elementId:function(e,t){var n="recording-"+e;return typeof t!="undefined"&&(n+="-"+t),n},initElementTag:function(){var e={};return e.play="play",e.publish="hide",e.unpublish="show",e.protect="lock",e.unprotect="unlock",e.edit="edit",e.process="process",e["import"]="import",e["delete"]="delete",e},initElementFAClass:function(){var e={};return e.publish="icon fa fa-eye-slash fa-fw iconsmall",e.unpublish="icon fa fa-eye fa-fw iconsmall",e.protect="icon fa fa-unlock fa-fw iconsmall",e.unprotect="icon fa fa-lock fa-fw iconsmall",e.edit="icon fa fa-pencil fa-fw iconsmall",e.process="icon fa fa-spinner fa-spin iconsmall",e["import"]="icon fa fa-download fa-fw iconsmall",e["delete"]="icon fa fa-trash fa-fw iconsmall",e},initElementActionReversed:function(){var e={};return e.play="play",e.publish="unpublish",e.unpublish="publish",e.protect="unprotect",e.unprotect="protect",e.edit="edit",e["import"]="import",e["delete"]="delete",e},reloadPreview:function(t){var n=e.one("#preview-"+t.recordingid).all("> img");n.each(function(e){var t=e.getAttribute("src");t=t.substring(0,t.indexOf("?")),t+="?"+(new Date).getTime(),e.setAttribute("src",t)})},capitalize:function(e){return e.charAt(0).toUpperCase()+e.slice(1)}}},"@VERSION@",{requires:["base","node","datasource-get","datasource-jsonschema","datasource-polling","moodle-core-notification"]});
