'use strict';
const toBoolean = require('./utils/to-boolean');
const getenv = require('./utils/getenv');
const fixApiHost = require('./utils/fix-api-host');

module.exports = function (environment) {
    let ENV = {
        modulePrefix: '@fleetbase/console',
        environment,
        rootURL: '/',
        locationType: 'history',
        EmberENV: {
            FEATURES: {
                // Here you can enable experimental features on an ember canary build
                // e.g. EMBER_NATIVE_DECORATOR_SUPPORT: true
            },
        },

        APP: {
            // Here you can pass flags/options to your application instance
            // when it is created
        },

        API: {
            host: fixApiHost(getenv('API_HOST'), getenv('API_SECURE')),
            namespace: getenv('API_NAMESPACE', 'int/v1'),
        },

        osrm: {
            host: getenv('OSRM_HOST', 'https://bundle.routing.fleetbase.io'),
            servers: getenv('OSRM_SERVERS', '').split(',').filter(Boolean),
        },

        socket: {
            path: getenv('SOCKETCLUSTER_PATH', '/socketcluster/'),
            hostname: getenv('SOCKETCLUSTER_HOST'),
            secure: toBoolean(getenv('SOCKETCLUSTER_SECURE', false)),
            port: getenv('SOCKETCLUSTER_PORT', 38000),
        },

        defaultValues: {
            categoryImage: getenv('DEFAULT_CATEGORY_IMAGE', 'https://flb-assets.s3.ap-southeast-1.amazonaws.com/images/fallback-placeholder-1.png'),
            placeholderImage: getenv('DEFAULT_PLACEHOLDER_IMAGE', 'https://flb-assets.s3.ap-southeast-1.amazonaws.com/images/fallback-placeholder-2.png'),
            driverImage: getenv('DEFAULT_DRIVER_IMAGE', 'https://s3.ap-southeast-1.amazonaws.com/flb-assets/static/no-avatar.png'),
            userImage: getenv('DEFAULT_USER_IMAGE', 'https://s3.ap-southeast-1.amazonaws.com/flb-assets/static/no-avatar.png'),
            contactImage: getenv('DEFAULT_CONTACT_IMAGE', 'https://s3.ap-southeast-1.amazonaws.com/flb-assets/static/no-avatar.png'),
            vehicleImage: getenv('DEFAULT_VEHICLE_IMAGE', 'https://flb-assets.s3.ap-southeast-1.amazonaws.com/static/vehicle-icons/light_commercial_van.svg'),
            vehicleAvatar: getenv('DEFAUL_VEHICLE_AVATAR', 'https://flb-assets.s3-ap-southeast-1.amazonaws.com/static/vehicle-icons/mini_bus.svg'),
        },

        'ember-simple-auth': {
            routeAfterAuthentication: 'console',
        },

        'ember-local-storage': {
            namespace: '@fleetbase',
            keyDelimiter: '/',
            includeEmberDataSupport: true,
        },
    };

    if (environment === 'development') {
        // ENV.APP.LOG_RESOLVER = true;
        // ENV.APP.LOG_ACTIVE_GENERATION = true;
        // ENV.APP.LOG_TRANSITIONS = true;
        // ENV.APP.LOG_TRANSITIONS_INTERNAL = true;
        // ENV.APP.LOG_VIEW_LOOKUPS = true;
    }

    if (environment === 'test') {
        // Testem prefers this...
        ENV.locationType = 'none';

        // keep test console output quieter
        ENV.APP.LOG_ACTIVE_GENERATION = false;
        ENV.APP.LOG_VIEW_LOOKUPS = false;

        ENV.APP.rootElement = '#ember-testing';
        ENV.APP.autoboot = false;
    }

    if (environment === 'production') {
        // here you can enable a production-specific feature
    }

    return ENV;
};
