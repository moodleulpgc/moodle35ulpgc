YUI.add('moodle-availability_timeelapsed-form', function (Y, NAME) {

/**
 * JavaScript for form editing timeelapsed conditions.
 *
 * @module moodle-availability_timeelapsed-form
 */
M.availability_timeelapsed = M.availability_timeelapsed || {};

/**
 * @class M.availability_timeelapsed.form
 * @extends M.core_availability.plugin
 */
M.availability_timeelapsed.form = Y.Object(M.core_availability.plugin);

/**
 * Groupings available for selection (alphabetical order).
 *
 * @property timeelapseds
 * @type Array
 */
M.availability_timeelapsed.form.timeelapseds = null;

/**
 * Initialises this plugin.
 *
 * @method initInner
 * @param {Array} standardFields Array of objects with .field, .display
 */
M.availability_timeelapsed.form.initInner = function(standardFields) {
    this.standardFields = standardFields;
};

M.availability_timeelapsed.form.getNode = function(json) {
    // Create HTML structure.
    var html = '<span class="availability-group"><label>' + M.util.get_string('conditiontitle', 'availability_timeelapsed') + ' ' +
            '<select name="field">' +
            '<option value="choose">' + M.util.get_string('choosedots', 'moodle') + '</option>';
    var fieldInfo;
    for (var i = 0; i < this.standardFields.length; i++) {
        fieldInfo = this.standardFields[i];
        // String has already been escaped using format_string.
        html += '<option value="rf_' + fieldInfo.field + '">' + fieldInfo.display + '</option>';
    }
    html += '</select></label> <label><span class="accesshide">' + M.util.get_string('label_operator', 'availability_timeelapsed') +
            ' </span><select name="op" title="' + M.util.get_string('label_operator', 'availability_timeelapsed') + '">';
    var operators = ['greater', 'atleast', 'less', 'atmost', 'equal'];
    for (i = 0; i < operators.length; i++) {
        html += '<option value="' + operators[i] + '">' +
                M.util.get_string('op_' + operators[i], 'availability_timeelapsed') + '</option>';
    }
    html += '</select></label> <label><span class="accesshide">' + M.util.get_string('label_value', 'availability_timeelapsed') +
            '</span><input name="value" type="text" style="width: 5em" title="' +
            M.util.get_string('label_value', 'availability_timeelapsed') + '"/></label></span>';
    html += M.util.get_string('days', 'availability_timeelapsed');
    var node = Y.Node.create('<span>' + html + '</span>');

    // Set initial values if specified.
    if (json.rf !== undefined &&
            node.one('select[name=field] > option[value=rf_' + json.rf + ']')) {
        node.one('select[name=field]').set('value', 'rf_' + json.rf);
    }
    if (json.op !== undefined &&
            node.one('select[name=op] > option[value=' + json.op + ']')) {
        node.one('select[name=op]').set('value', json.op);
    }
    if (json.v !== undefined) {
        node.one('input').set('value', json.v);
    }

    // Add event handlers (first time only).
    if (!M.availability_timeelapsed.form.addedEvents) {
        M.availability_timeelapsed.form.addedEvents = true;
        var updateForm = function(input) {
            var ancestorNode = input.ancestor('span.availability_timeelapsed');
            var op = ancestorNode.one('select[name=op]');
            var novalue = (op.get('value') === 'isempty' || op.get('value') === 'isnotempty');
            ancestorNode.one('input[name=value]').set('disabled', novalue);
            M.core_availability.form.update();
        };
        var root = Y.one('#fitem_id_availabilityconditionsjson');
        root.delegate('change', function() {
             updateForm(this);
        }, '.availability_timeelapsed select');
        root.delegate('change', function() {
             updateForm(this);
        }, '.availability_timeelapsed input[name=value]');
    }

    return node;
};

M.availability_timeelapsed.form.fillValue = function(value, node) {
    // Set field.
    var field = node.one('select[name=field]').get('value');
    if (field.substr(0, 3) === 'rf_') {
        value.rf = field.substr(3);
    }

    // Operator and value
    value.op = node.one('select[name=op]').get('value');
    var valueNode = node.one('input[name=value]');
    if (!valueNode.get('disabled')) {
        value.v = valueNode.get('value');
    }
};

M.availability_timeelapsed.form.fillErrors = function(errors, node) {
    var value = {};
    this.fillValue(value, node);

    // Check timeelapsed item id.
    if (value.rf === undefined) {
        errors.push('availability_timeelapsed:error_selectfield');
    }
    if (value.v !== undefined && /^\s*$/.test(value.v)) {
        errors.push('availability_timeelapsed:error_setvalue');
    }
};

}, '@VERSION@', {"requires": ["base", "node", "event", "io", "moodle-core_availability-form"]});
