import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { inject as service } from '@ember/service';
import { computed } from '@ember/object';
import { isBlank } from '@ember/utils';

export default class LogoIconComponent extends Component {
    @service store;
    @tracked size = 8;
    @tracked brand;
    @tracked ready = false;

    sizeMap = {
        4: 16,
        5: 20,
        8: 32,
        10: 40,
        12: 48,
        16: 64,
        20: 80,
    };

    @computed('size', 'sizeMap') get px() {
        return this.sizeMap[this.size];
    }

    constructor() {
        super(...arguments);
        this.size = this.getSize();

        if (isBlank(this.args.brand)) {
            this.loadIcon();
        } else {
            this.brand = this.args.brand;
            this.ready = true;
        }
    }

    getSize() {
        let size = this.args.size;

        if (size) {
            return parseInt(size);
        }

        return this.size;
    }

    loadIcon() {
        this.store
            .findRecord('brand', 1)
            .then((brand) => {
                this.brand = brand;
            })
            .finally(() => {
                this.ready = true;
            });
    }
}
