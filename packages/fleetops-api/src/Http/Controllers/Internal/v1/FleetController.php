<?php

namespace Fleetbase\FleetOps\Http\Controllers\Internal\v1;

use Fleetbase\FleetOps\Http\Controllers\FleetOpsController;
use Fleetbase\FleetOps\Exports\FleetExport;
use Fleetbase\FleetOps\Http\Requests\Internal\FleetActionRequest;
use Fleetbase\FleetOps\Models\Driver;
use Fleetbase\FleetOps\Models\Fleet;
use Fleetbase\FleetOps\Models\FleetDriver;
use Fleetbase\Http\Requests\ExportRequest;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class FleetController extends FleetOpsController
{
    /**
     * The resource to query
     *
     * @var string
     */
    public $resource = 'fleet';

    /**
     * Export the fleets to excel or csv
     *
     * @param  \Illuminate\Http\Request  $query
     * @return \Illuminate\Http\Response
     */
    public static function export(ExportRequest $request)
    {
        $format = $request->input('format', 'xlsx');
        $fileName = trim(Str::slug('fleets-' . date('Y-m-d-H:i')) . '.' . $format);

        return Excel::download(new FleetExport(), $fileName);
    }

    /**
     * Removes a driver from a fleet
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public static function removeDriver(FleetActionRequest $request)
    {
        $fleet = Fleet::where('uuid', $request->input('fleet'))->first();
        $driver = Driver::where('uuid', $request->input('driver'))->first();

        // check if driver is already in this fleet
        $deleted = FleetDriver::where([
            'fleet_uuid' => $fleet->uuid,
            'driver_uuid' => $driver->uuid,
        ])->delete();

        return response()->json([
            'status' => 'ok',
            'deleted' => $deleted
        ]);
    }

    /**
     * Adds a driver to a fleet
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public static function addDriver(FleetActionRequest $request)
    {
        $fleet = Fleet::where('uuid', $request->input('fleet'))->first();
        $driver = Driver::where('uuid', $request->input('driver'))->first();
        $added = false;

        // check if driver is already in this fleet
        $exists = FleetDriver::where([
            'fleet_uuid' => $fleet->uuid,
            'driver_uuid' => $driver->uuid,
        ])->exists();

        if (!$exists) {
            $added = FleetDriver::create([
                'fleet_uuid' => $fleet->uuid,
                'driver_uuid' => $driver->uuid,
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'exists' => $exists,
            'added' => (bool) $added
        ]);
    }
}
