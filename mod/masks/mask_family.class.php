<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * mask_family base class
 *
 * @copyright  2016 Edunao SAS (contact@edunao.com)
 * @author     Sadge (daniel@edunao.com)
 * @package    mod_masks
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace mod_masks;

defined('MOODLE_INTERNAL') || die;

abstract class mask_family{

    // -------------------------------------------------------------------------
    // Protected Data

    protected $familyName   = null;
    protected $masksStyles  = null;
    protected $cssfile      = null;


    // -------------------------------------------------------------------------
    // public helper functions

    public function getStylesTag( ){
        return '<link rel="stylesheet" type="text/css" href="' . $this->cssfile . '">';
    }

    public function getCssFile(){
        return $this->cssfile;
    }

    public function getMasksStyles(){
        return $this->masksStyles;
    }

    public function getStyleClass(){
        return 'mask-family-' . $this->familyName;
    }

    public function getFamilyName(){
        return $this->familyName;
    }
}
