import Service from '@ember/service';
import { tracked } from '@glimmer/tracking';
import { isBlank } from '@ember/utils';
import toBoolean from '../utils/to-boolean';
import config from 'ember-get-config';

export default class SocketService extends Service {
    @tracked channels = [];

    constructor() {
        super(...arguments);
        this.socket = this.createSocketClusterClient();
    }

    instance() {
        return this.socket;
    }

    createSocketClusterClient() {
        const socketConfig = { ...config.socket };

        if (isBlank(socketConfig.hostname)) {
            socketConfig.hostname = window.location.hostname;
        }

        socketConfig.secure = toBoolean(socketConfig.secure);

        return socketClusterClient.create(socketConfig);
    }

    async listen(channelId, callback) {
        const channel = this.socket.subscribe(channelId);

        // track channel
        this.channels.pushObject(channel);

        // listen to channel for events
        await channel.listener('subscribe').once();

        // get incoming data and console out
        for await (let output of channel) {
            if (typeof callback === 'function') {
                callback(output);
            }
        }
    }

    closeChannels() {
        for (let i = 0; i < this.channels.length; i++) {
            const channel = this.channels.objectAt(i);

            channel.close();
        }
    }
}
