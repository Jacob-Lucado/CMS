<?php

namespace App\Emulator\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Common model shape exposed by every emulator rank implementation.
 *
 * Drivers may store these values in real columns or expose them through
 * accessors, but shared CMS code can rely on one stable model contract.
 *
 * @property int $id
 * @property string|null $rank_name
 * @property string $badge
 * @property string $staff_color
 * @property string $staff_background
 * @property string $job_description
 */
abstract class Rank extends Model {}
