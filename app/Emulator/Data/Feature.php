<?php

namespace App\Emulator\Data;

/**
 * Shared CMS surface that only works when the emulator stores the data behind
 * it. Anything a driver can express through a repository is not a Feature - it
 * works everywhere by definition.
 *
 * Housekeeping resources bound to one emulator's tables are not Features
 * either: each driver ships its own resource gated with RequiresEmulatorDriver.
 * A Feature is for shared surface that has to disappear entirely.
 */
enum Feature: string
{
    case CommandLogs = 'command-logs';
    case Wordfilter = 'wordfilter';
    case NameChangePermission = 'name-change-permission';
    case RareValues = 'rare-values';
    case CameraPhotos = 'camera-photos';
}
