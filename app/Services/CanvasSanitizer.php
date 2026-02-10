<?php

namespace App\Services;

class CanvasSanitizer
{
    /**
     * Remove unexpected fields and normalize Fabric payload.
     */
    public function sanitize(array $canvas): array
    {
        $allowedObjectKeys = [
            'type', 'version', 'originX', 'originY', 'left', 'top', 'width', 'height',
            'fill', 'stroke', 'strokeWidth', 'scaleX', 'scaleY', 'angle', 'opacity',
            'flipX', 'flipY', 'visible', 'backgroundColor', 'text', 'fontFamily',
            'fontSize', 'fontStyle', 'fontWeight', 'textAlign', 'charSpacing', 'src',
            'path', 'radius', 'x1', 'x2', 'y1', 'y2', 'selectable', 'lockMovementX',
            'lockMovementY', 'lockRotation', 'lockScalingX', 'lockScalingY', 'name',
            'id', 'globalCompositeOperation',
        ];

        $canvas['objects'] = collect($canvas['objects'] ?? [])
            ->map(function (array $object) use ($allowedObjectKeys): array {
                return array_intersect_key($object, array_flip($allowedObjectKeys));
            })
            ->values()
            ->all();

        return $canvas;
    }
}
