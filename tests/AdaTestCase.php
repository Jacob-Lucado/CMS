<?php

namespace Tests;

/**
 * Runs a test against a database Ada owns outright.
 *
 * Ada and Arcturus share table names - catalog_pages, catalog_items, rooms and
 * permissions among them - so one database can only ever hold one of the two
 * shapes. Tests that would otherwise resolve Ada's models against Arcturus
 * tables belong here, where the schema is the real EF one.
 */
abstract class AdaTestCase extends TestCase
{
    protected function databaseName(): string
    {
        return (string) env('DB_ADA_DATABASE', 'testing_ada');
    }

    protected function emulatorDriver(): string
    {
        return 'ada';
    }

    /**
     * Ada ships no RCON bridge, so there is no socket to stand in for and
     * substituting one would hide how the driver actually behaves.
     */
    protected function fakesRcon(): bool
    {
        return false;
    }
}
