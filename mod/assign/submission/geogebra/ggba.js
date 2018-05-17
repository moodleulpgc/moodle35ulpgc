/**
 * Created by Christoph on 09.05.14.
 */

ggbparametersin = Y.one('input[name="ggbparameters"]');
ggbviewsin = Y.one('input[name="ggbviews"]');
ggbcodebaseversionin = Y.one('input[name="ggbcodebaseversion"]');

Y.on('submit', function (e) {
    if (!(typeof applet1 === 'undefined') && !(typeof ggbApplet === 'undefined')) {
        ggbparametersin.set('value', JSON.stringify(applet1.getParameters()));
        parameters.ggbBase64 = ggbApplet.getBase64();
        ggbparametersin.set('value', JSON.stringify(parameters));
        ggbviewsin.set('value', JSON.stringify(applet1.getViews()));
        ggbcodebaseversionin.set('value', applet1.getHTML5CodebaseVersion());
    }
}, '#mform1');
Y.on('mouseleave', function (e) {
    if (!(typeof applet1 === 'undefined') && !(typeof ggbApplet === 'undefined')) {
        ggbparametersin.set('value', JSON.stringify(applet1.getParameters()));
        parameters.ggbBase64 = ggbApplet.getBase64();
        if (JSON.stringify(parameters) != ggbparametersin.get('value')) {
            M.core_formchangechecker.set_form_changed();
        }
        ggbparametersin.set('value', JSON.stringify(parameters));
        ggbviewsin.set('value', JSON.stringify(applet1.getViews()));
        ggbcodebaseversionin.set('value', applet1.getHTML5CodebaseVersion());
    }
}, '#applet_container1');

function ggbAppletOnLoad(ggbAppletId) {
    //document.querySelector('article').onkeypress = checkEnter;
    document.querySelector('article').onkeydown = checkEnter;
    ggbparametersin.set('value', JSON.stringify(applet1.getParameters()));
    ggbviewsin.set('value', JSON.stringify(applet1.getViews()));
    ggbcodebaseversionin.set('value', applet1.getHTML5CodebaseVersion());
}

function checkEnter(e) {
    e = e || event;
    var txtArea = /textarea/i.test((e.target || e.srcElement).tagName);
    return txtArea || (e.keyCode || e.which || e.charCode || 0) !== 13;
}
