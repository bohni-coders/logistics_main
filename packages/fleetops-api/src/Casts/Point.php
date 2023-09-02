<?php

namespace Fleetbase\FleetOps\Casts;

use Fleetbase\FleetOps\Support\Utils;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Grimzy\LaravelMysqlSpatial\Types\GeometryInterface;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialExpression;
use Illuminate\Database\Query\Expression;

class Point implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($model, $key, $value, $attributes)
    {
        if (static::isRawPoint($value)) {
            return Utils::rawPointToPoint($value);
        }

        return $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set($model, $key, $value, $attributes)
    {
        if ($value instanceof Expression) {
            return $value;
        }

        if ($value instanceof GeometryInterface) {
            $model->geometries[$key] = $value;

            return new SpatialExpression($value);
        }

        if (Utils::isGeoJson($value)) {
            return Utils::createSpatialExpressionFromGeoJson($value);
        }

        if (Utils::isCoordinates($value)) {
            $point = Utils::getPointFromCoordinates($value);

            return $point;
        }

        return new SpatialExpression(new Point(0, 0));
    }

    public static function coordinatesBboxToFloat(array $geometry)
    {
        foreach ($geometry as $key => $value) {
            if (in_array($key, ['coordinates', 'bbox']) && is_array($value)) {
                foreach ($value as $index => $value) {
                    $geometry[$key][$index] = (float) $value;
                }
            }
        }

        return $geometry;
    }

    public static function isRawPoint($data)
    {
        return preg_match('/[\x00-\x1f]/', $data, $matches) === 1;
    }

    public static function hex2str($hex)
    {
        $str = '';
        for ($i = 0; $i < strlen($hex); $i += 2) $str .= chr(hexdec(substr($hex, $i, 2)));
        return $str;
    }
}
