// BEGIN-SNIPPET delete-category
import Fleetbase from '@fleetbase/sdk';

const fleetbase = new Fleetbase('public_key');
fleetbase.categorys.destroyRecord('category_id');
// END-SNIPPET
