import Component from '@glimmer/component';
import { inject as service } from '@ember/service';
import { computed, action } from '@ember/object';
import { isBlank } from '@ember/utils';
import { bool } from '@ember/object/computed';
import isMenuItemActive from '../../../../utils/is-menu-item-active';

export default class LayoutHeaderDropdownItemComponent extends Component {
    @service router;
    @service hostRouter;

    @bool('args.item.onClick') isInteractive;
    @bool('args.item.href') isAnchor;
    @bool('args.item.seperator') isSeperator;

    @computed('args.item.{route,onClick}') get isLink() {
        return typeof this.args.item.route === 'string' && typeof this.args.item.onClick !== 'function';
    }

    @computed('args.item.{component,onClick}') get isComponent() {
        return typeof this.args.item.component === 'string' && typeof this.args.item.onClick !== 'function';
    }

    @computed('args.item.text', 'isAnchor', 'isLink', 'isComponent', 'isSeperator', 'isInteractive')
    get isTextOnly() {
        const { isAnchor, isLink, isComponent, isSeperator, isInteractive } = this;
        const { text } = this.args.item;

        return [isAnchor, isLink, isComponent, isSeperator, isInteractive].every((prop) => prop === false) && text;
    }

    @computed('args.route', 'hostRouter.currentRouteName', 'router.currentRouteName', 'isInteractive')
    get active() {
        const { route, item } = this.args;
        const router = this.getRouter();
        const currentRoute = router.currentRouteName;

        // if interactive use
        if (this.isInteractive && !isBlank(item)) {
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
