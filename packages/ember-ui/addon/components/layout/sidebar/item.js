import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { inject as service } from '@ember/service';
import { action } from '@ember/object';
import { isBlank } from '@ember/utils';
import isMenuItemActive from '../../../utils/is-menu-item-active';

export default class LayoutSidebarItemComponent extends Component {
    @service router;
    @service hostRouter;
    @tracked active;

    constructor() {
        super(...arguments);
        this.active = this.checkIfActive();
        const router = this.getRouter();
        router.on('routeDidChange', this.trackActiveFlag);
    }

    willDestroy() {
        super.willDestroy(...arguments);
        const router = this.getRouter();
        router.off('routeDidChange', this.trackActiveFlag);
    }

    @action trackActiveFlag() {
        this.active = this.checkIfActive();
    }

    @action checkIfActive() {
        const { route, onClick, item } = this.args;
        const router = this.getRouter();
        const currentRoute = router.currentRouteName;
        const isInteractive = isBlank(route) && typeof onClick === 'function';

        if (isInteractive && !isBlank(item)) {
            return isMenuItemActive(item.section, item.slug, item.view);
        }

        return typeof route === 'string' && currentRoute.startsWith(route);
    }

    @action onClick(event) {
        const { url, target, route, model, onClick, options } = this.args;
        const router = this.getRouter();
        const anchor = event.target?.closest('a');

        if (anchor && anchor.attributes?.disabled && anchor.attributes.disabled !== 'disabled="false"') {
            return;
        }

        if (target && url) {
            return window.open(url, target);
        }

        if (url) {
            return (window.location.href = url);
        }

        if (typeof onClick === 'function') {
            return onClick();
        }

        if (!isBlank(options) && route && model) {
            return router.transitionTo(route, model, options);
        }

        if (!isBlank(options) && route) {
            return router.transitionTo(route, options);
        }

        if (route && model) {
            return router.transitionTo(route, model);
        }

        if (route) {
            return router.transitionTo(route);
        }
    }

    getRouter() {
        return this.router ?? this.hostRouter;
    }
}
