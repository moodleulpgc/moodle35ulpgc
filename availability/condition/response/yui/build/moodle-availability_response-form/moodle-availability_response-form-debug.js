YUI.add('moodle-availability_response-form', function (Y, NAME) {

/**
 * JavaScript for form editing response conditions.
 *
 * @module moodle-availability_response-form
 */
M.availability_response = M.availability_response || {};

/**
 * @class M.availability_response.form
 * @extends M.core_availability.plugin
 */
M.availability_response.form = Y.Object(M.core_availability.plugin);

/**
 * Groupings available for selection (alphabetical order).
 *
 * @property responses
 * @type Array
 */
M.availability_response.form.responses = null;

/**
 * Initialises this plugin.
 *
 * @method initInner
 * @param {Array} choiceInstances Array of objects with .field, .display
 */
M.availability_response.form.initInner = function(choiceInstances) {
    this.choiceInstances = choiceInstances;
};

M.availability_response.form.getNode = function(json) {
    // Create HTML structure.
    var html = '<span class="availability-group"><label>' + M.util.get_string('conditiontitle', 'availability_response') + ' ' +
            '<select name="field">' +
            '<option value="choose">' + M.util.get_string('choosedots', 'moodle') + '</option>';
    var fieldInfo;
    for (var i = 0; i < this.choiceInstances.length; i++) {
        fieldInfo = this.choiceInstances[i];
        // String has already been escaped using format_string.
        html += '<option value="rf_' + fieldInfo.field + '">' + fieldInfo.display + '</option>';
    }
    html += '</select></label> <label><span class="accesshide">' + M.util.get_string('label_operator', 'availability_response') +
            ' </span><select name="op" title="' + M.util.get_string('label_operator', 'availability_response') + '">';
    var operators = ['isequalto', 'contains', 'doesnotcontain', 'startswith', 'endswith'];
    for (i = 0; i < operators.length; i++) {
        html += '<option value="' + operators[i] + '">' +
                M.util.get_string('op_' + operators[i], 'availability_response') + '</option>';
    }
    html += '</select></label> <label><span class="accesshide">' + M.util.get_string('label_value', 'availability_response') +
            '</span><input name="value" type="text" style="width: 10em" title="' +
            M.util.get_string('label_value', 'availability_response') + '"/></label></span>';
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
    if (!M.availability_response.form.addedEvents) {
        M.availability_response.form.addedEvents = true;
        var updateForm = function(input) {
            var ancestorNode = input.ancestor('span.availability_response');
            var op = ancestorNode.one('select[name=op]');
            var novalue = (op.get('value') === 'isempty' || op.get('value') === 'isnotempty');
            ancestorNode.one('input[name=value]').set('disabled', novalue);
            M.core_availability.form.update();
        };
        var root = Y.one('#fitem_id_availabilityconditionsjson');
        root.delegate('change', function() {
             updateForm(this);
        }, '.availability_response select');
        root.delegate('change', function() {
             updateForm(this);
        }, '.availability_response input[name=value]');
    }

    return node;
};

M.availability_response.form.fillValue = function(value, node) {
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

M.availability_response.form.fillErrors = function(errors, node) {
    var value = {};
    this.fillValue(value, node);

    // Check response item id.
    if (value.rf === undefined) {
        errors.push('availability_response:error_selectfield');
    }
    if (value.v !== undefined && /^\s*$/.test(value.v)) {
        errors.push('availability_response:error_setvalue');
    }
};

}, '@VERSION@', {"requires": ["base", "node", "event", "io", "moodle-core_availability-form"]});
