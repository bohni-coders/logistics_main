import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action, set, computed } from '@ember/object';
import { guidFor } from '@ember/object/internals';

export default class TableCellCheckboxComponent extends Component {
    /**
     * Generates a unique ID for this checkbox instance
     *
     * @var {String}
     */
    @computed('args.row.id') get id() {
        const { row } = this.args;

        if (row?.id) {
            return row.id;
        }

        return guidFor(this);
    }

    /**
     * Whether this checkbox is checked or not
     *
     * @param {Boolean} checked
     */
    @tracked checked = false;

    /**
     * Toggles the checkbox and sends up an action
     *
     * @void
     */
    @action onToggle(checked) {
        const { row, column, onToggle } = this.args;
        const checkedProperty = column?.valuePath;

        this.checked = checked;

        if (row) {
            if (checkedProperty) {
                set(row, checkedProperty, checked);
            }
            set(row, 'checked', checked);
        }

        if (typeof column?.onToggle === 'function') {
            column.onToggle(checked, row);
        }

        if (typeof onToggle === 'function') {
            onToggle(checked, row);
        }
    }
}
