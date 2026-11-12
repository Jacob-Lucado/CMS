<?php

namespace App\Emulator\Data;

/**
 * Schema mutations in shared historical migrations. Drivers opt into only
 * the migrations that target tables they own.
 */
enum SchemaFeature: string
{
    case PermissionMetadata = 'permission-metadata';
    case ItemIdIndex = 'item-id-index';
}
