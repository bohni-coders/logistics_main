import Controller from '@ember/controller';
import { inject as service } from '@ember/service';
import { alias } from '@ember/object/computed';
import { action, set } from '@ember/object';
import { capitalize } from '@ember/string';
import getGatewaySchemas from '../../utils/get-gateway-schemas';

export default class SettingsGatewaysController extends Controller {
    @service notifications;
    @service modalsManager;
    @service store;
    @service crud;
    @service storefront;
    @alias('storefront.activeStore') activeStore;

    @action createGateway() {
        const gateway = this.store.createRecord('gateway', {
            owner_uuid: this.activeStore.id,
            owner_type: 'storefront:store',
        });

        this.editGateway(gateway, {
            title: `Create a new payment gateway`,
            acceptButtonText: 'Save Gateway',
            decline: (modal) => {
                gateway.destroyRecord();
                modal.done();
            },
            successNotification: (gateway) => `New gateway (${gateway.name}) created.`,
            onConfirm: () => {
                if (gateway.get('isNew')) {
                    return;
                }

                this.gateways.pushObject(gateway);
            },
        });
    }

    @action editGateway(gateway, options = {}) {
        const schemas = getGatewaySchemas();
        const schemaOptions = Object.keys(schemas);

        this.modalsManager.show('modals/create-gateway', {
            title: `Edit payment gateway`,
            acceptButtonText: 'Save Changes',
            schema: null,
            schemas,
            schemaOptions,
            gateway,
            selectSchema: (schema) => {
                this.modalsManager.setOption('schema', schemas[schema]);

                gateway.setProperties({
                    name: `${capitalize(schema)} Gateway`,
                    code: schema,
                    config: schemas[schema],
                    type: schema,
                });
            },
            setConfigKey: (key, value) => {
                // eslint-disable-next-line no-undef
                if (value instanceof Event) {
                    const eventValue = value.target.value;

                    set(gateway.config, key, eventValue);
                    return;
                }

                set(gateway.config, key, value);
            },
            confirm: (modal, done) => {
                modal.startLoading();

                gateway
                    .save()
                    .then((gateway) => {
                        if (typeof options.successNotification === 'function') {
                            this.notifications.success(options.successNotification(gateway));
                        } else {
                            this.notifications.success(options.successNotification || `${gateway.name} details updated.`);
                        }

                        done();
                    })
                    .catch((error) => {
                        // gateway.rollbackAttributes();
                        modal.stopLoading();
                        this.notifications.serverError(error);
                    });
            },
            ...options,
        });
    }

    @action deleteGateway(gateway) {
        this.modalsManager.confirm({
            title: 'Are you sure you wish to remove this gateway?',
            body: 'All applications and websites utilizing this gateway in configuration could be disrupted if gateway is not replaced beforehand',
            confirm: (modal) => {
                modal.startLoading();

                return gateway.destroyRecord().then(() => {
                    modal.stopLoading();
                });
            },
        });
    }
}
