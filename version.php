<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Version details for the Synergy Learning theme.
 *
 * @package    theme_synergylearning
 * @copyright  2026 Kaori Nishimura
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'theme_synergylearning';  // Plugin type + name. Must match the folder name.
$plugin->version   = 2026101707;               // YYYYMMDDXX. Raise it whenever settings or strings change.
$plugin->release   = '0.1.0';
$plugin->maturity  = MATURITY_ALPHA;
$plugin->requires  = 2026100500;               // Moodle 5.3.
$plugin->supported = [503, 503];
$plugin->dependencies = [
    'theme_boost' => 2026100500, // The parent theme must be installed.
];
